<?php

namespace modules\bee_invasion\v1\api\game\economy;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\economy\OrderBillDao;
use modules\bee_invasion\v1\dao\game\economy\OrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\PriceItem;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\role\RoleEquipment;
use modules\bee_invasion\v1\model\role\RoleEquipmentHis;
use modules\bee_invasion\v1\model\tool\BiParam;
use modules\bee_invasion\v1\model\user\UserCg;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionCreateOrder extends GameBaseAction
{
    public function run()
    {

        $row_pk     = $this->inputDataBox->getIntNotNull('price_item_id');
        $pkg_amount = $this->inputDataBox->getIntNotNull('amount');
        $price_item = PriceItem::model()->getModelByPk($row_pk);
        if ($price_item->pay_item_code !== 'cash')
        {
            throw new AdvError(AdvError::data_info_unexpected, '您选中的商品，不是现金支付的', [$price_item->pay_item_code]);
        }
        $price     = $price_item->pay_item_amount;
        $order_sum = bcdiv(bcmul($price, $pkg_amount), BiParam::dbNumberRate);
        Sys::app()->addLog([$order_sum, $price, $pkg_amount], '$order_sum');

        $goods_list = [];
        if ($price_item->goods_item_class[0] === 'cg')
        {
            $goods_list['cg']   = [];
            $goods_list['cg'][] = $price_item->goods_item_code;
        }
        else if ($price_item->goods_item_class[0] === 'currency')
        {
            $goods_list['currency']   = [];
            $goods_list['currency'][] = $price_item->goods_item_code;
        }
        else if ($price_item->goods_item_class[0] === 'equipment')
        {
            $goods_list['equipment'] = [];
            $goods_item              = Equipment::model()->getItemByCode($price_item->goods_item_code);
            $goods_account           = RoleEquipment::model()->setUser($this->user)->getAccount($price_item->goods_item_code);
            if ($goods_account->item_amount > 0)
            {
                throw new AdvError(AdvError::data_info_unexpected, "您已经拥有了[{$goods_item->item_name}]，不能再次购买了");
            }
            $goods_list['equipment'][] = $price_item->goods_item_code;
        }
        else
        {
            throw new AdvError(AdvError::data_info_unexpected);
        }
        try
        {
            $order                   = new OrderDao();
            $order->open_id          = md5($this->user->id . time());
            $order->user_id          = $this->user->id;
            $order->user_token       = '';
            $order->ip               = '';
            $order->detail           = $price_item->item_detail;
            $order->title            = $price_item->goods_name . ' * ' . $pkg_amount;
            $order->order_sum        = $order_sum;
            $order->payment_platform = 'self';
            $order->payment_code     = $price_item->pay_item_code;
            $order->order_type       = 1;
            $order->update_time      = date('Y-m-d H:i:s', time());
            $order->is_payed         = Opt::isNotOk;

            $order->insert(true);

            $bill               = new OrderBillDao();
            $bill->order_id     = $order->id;
            $bill->user_id      = $this->user->id;
            $bill->bill_sum     = $order_sum;
            $bill->payment_code = $price_item->pay_item_code;
            $bill->goods_amount = $price_item->goods_item_amount * $pkg_amount;
            $bill->goods_code   = $price_item->goods_item_code;
            $bill->goods_class  = $price_item->goods_item_class;
            $bill->price_detail = [$price_item->pay_item_amount, $price_item->goods_item_amount];
            $bill->update_time  = date('Y-m-d H:i:s', time());
            $bill->insert(true);
        } catch (\Exception $e)
        {
            throw  new AdvError(AdvError::pay_error_create_order_fail);
        }


        $order_info              = $order->getOpenInfo();
        $order_info['goodsList'] = $goods_list;

        $plat_order               = new PlatOrderDao();
        $plat_order->order_src    = 'bee_invasion';
        $plat_order->src_order_id = $order->id;
        $plat_order->title        = $order->title;
        $plat_order->detail       = $order->detail;
        $plat_order->order_sum    = $order_sum * 100;
        $plat_order->device_ip    = $this->getRemoteIp();
        $plat_order->notify_url   = Sys::app()->params['pay']['pay_page_domain'] . '/bee_invasion/v1/game/economy/notify';
        $plat_order->return_url   = '';
        $plat_order->pay_channel  = 'alipay';
        try
        {
            $res = $plat_order->generateOrder();
            if (empty($res))
            {
                $res = $plat_order->generateOrder();
            }
            if (empty($res))
            {
                $res = $plat_order->generateOrder();
            }
            if (empty($res))
            {
                throw  new AdvError(AdvError::pay_error_create_payment_fail);
            }

        } catch (\Exception $e)
        {
            throw  new AdvError(AdvError::pay_error_create_payment_fail, '创建支付平台订单失败');
        }

        if (empty($res))
        {
            throw  new AdvError(AdvError::pay_error_create_payment_fail);
        }
        $plat_order->cacheOpenInfo();
        $pay_url               = $plat_order->getPayUrl();
        $order_info['pay_url'] = $pay_url;
        return $order_info;

    }
}
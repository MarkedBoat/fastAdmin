<?php

namespace modules\bee_invasion\v1\api\game\economy;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\economy\OrderBillDao;
use modules\bee_invasion\v1\dao\game\economy\OrderDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\Order;
use modules\bee_invasion\v1\model\economy\PriceItem;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\role\RoleEquipment;
use modules\bee_invasion\v1\model\role\RoleEquipmentHis;
use modules\bee_invasion\v1\model\TItem;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCg;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionExchange extends GameBaseAction
{
    /**
     * @return array
     * @throws AdvError
     */
    public function run()
    {
        $flag      = $this->user->id . '_' . intval(time() / 3);
        $limit_key = $this->user->getCacheKey('OrderUnique', ['flag' => $flag]);
        if (!$this->user->getRedis()->setnx($limit_key, 1))
        {
            Sys::app()->addLog([$limit_key, $this->user->getRedis()->get($limit_key), $this->user->getRedis()->ttl($limit_key)], 'xx');
            throw new AdvError(AdvError::user_op_err_to_fast, '您操作过快，请稍后重试');
        }
        $this->user->getRedis()->expire($limit_key, 3);
        $row_pk     = $this->inputDataBox->getIntNotNull('price_item_id');
        $pkg_amount = $this->inputDataBox->getIntNotNull('amount');
        if ($pkg_amount < 0)
        {
            throw new AdvError(AdvError::request_param_verify_fail, '请输入正常数值');
        }
        $price_item = PriceItem::model()->getModelByPk($row_pk);


        $pay_account  = UserCurrency::model()->setUser($this->user)->getAccount($price_item->pay_item_code);
        $user_balance = $pay_account->item_amount;
        $price        = $price_item->pay_item_amount;
        $order_sum    = $price * $pkg_amount;
        Sys::app()->addLog([$pay_account], '$pay_account');
        if ($user_balance < $order_sum)
        {
            throw new AdvError(AdvError::user_money_not_enough_to_pay, '您的余额不足以支付', ['user_balance' => $user_balance, 'order_sum' => $order_sum]);
        }

        $pay_src_flag   = '';
        $goods_src_flag = '';
        $goods_list     = [];
        if ($price_item->goods_item_class[0] === 'cg')
        {
            $goods_list['cg']   = [];
            $goods_item         = ConsumableGoods::model()->getItemByCode($price_item->goods_item_code);
            $goods_account      = UserCg::model()->setUser($this->user)->getAccount($price_item->goods_item_code);
            $goods_his          = (new UserCgHis())->setUserAccountModel($goods_account)->setOperationStep(1);
            $goods_src_flag     = UserCgHis::srcOrderGoods;
            $pay_src_flag       = UserCgHis::srcOrderPay;
            $goods_list['cg'][] = $price_item->goods_item_code;
            ApiCache::model()->setCache('ChangeFlagUserCg', ['user_id' => $this->user->id], time());

        }
        else if ($price_item->goods_item_class[0] === 'currency')
        {
            $goods_list['currency']   = [];
            $goods_item               = Currency::model()->getItemByCode($price_item->goods_item_code);
            $goods_account            = UserCurrency::model()->setUser($this->user)->getAccount($price_item->goods_item_code);
            $goods_his                = (new UserCurrencyHis())->setUserAccountModel($goods_account)->setOperationStep(1);
            $goods_src_flag           = UserCurrencyHis::srcOrderGoods;
            $pay_src_flag             = UserCurrencyHis::srcOrderPay;
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
            $goods_his                 = (new RoleEquipmentHis())->setUserAccountModel($goods_account)->setOperationStep(1);
            $goods_src_flag            = RoleEquipmentHis::srcOrderGoods;
            $pay_src_flag              = UserCurrencyHis::srcOrderPay;
            $goods_list['equipment'][] = $price_item->goods_item_code;
            ApiCache::model()->setCache('ChangeFlagUserEquipment', ['user_id' => $this->user->id], time());
        }
        else
        {
            throw new AdvError(AdvError::data_info_unexpected);
        }

        $order                   = new Order();
        $order->open_id          = md5($this->user->id . time());
        $order->user_id          = $this->user->id;
        $order->user_token       = '';
        $order->ip               = '';
        $order->detail           = $price_item->item_detail;
        $order->title            = $goods_item->item_name . ' * ' . $pkg_amount;
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

        $order->is_payed   = Opt::isOk;
        $order->payed_time = date('Y-m-d H:i:s', time());
        $order->update(true);

        //下面的工作  需要分离的
        $pay_account->verifyKeyProperties();
        $goods_account->verifyKeyProperties();
        $pay_his        = (new UserCurrencyHis())->setUserAccountModel($pay_account)->setOperationStep(1);
        $pay_record_res = $pay_his->tryRecord($pay_src_flag, $order->id, $order_sum);
        if ($pay_record_res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录失败', false, false);
        }

        $goods_his->setOperationStep(1);

        $goods_record_res = $goods_his->tryRecord($goods_src_flag, $order->id, $bill->goods_amount);
        if ($goods_record_res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录票据失败', false, false);
        }

        $pay_account->addPoints($order_sum, UserCurrencyHis::srcOrderGoods, $order->id);


        $pay_his->setOperationStep(3)->update(true, false);

        if ($pay_account)
        {
            if ($price_item->pay_item_code === 'gold_ingot')
            {
                echo "\n记录 bill 是金元宝 需要给与邀请人奖励\n";
                $order->addAsyncTask();
            }
        }
        $order->is_complete = Opt::isOk;
        $order->update();

        // UserCg::model()->setUser($this->user)->getAccount($price_item->goods_item_class[0])
        $order_info              = $order->getOpenInfo();
        $order_info['goodsList'] = $goods_list;
        $pay_account->reloadDbData();
        $order_info['userCurrency']    = [$price_item->pay_item_code => $pay_account->item_amount];
        $order_info['userInfoChanged'] = $this->user->getChangedCodes();
        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $this->user->id], time());
        return $order_info;

    }
}
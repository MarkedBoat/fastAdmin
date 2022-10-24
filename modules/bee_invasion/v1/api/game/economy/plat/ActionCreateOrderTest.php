<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\api\open\OpenBaseAction;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;


class ActionCreateOrderTest extends ActionBase
{


    public $requestMethods = ['POST'];
    public $dataSource     = 'JSON_STRING';
    /**
     * @var $partner PlatSrcDao
     */
    protected $partner;

    public function run()
    {

        if ($this->inputDataBox->getStringNotNull('test') !== 'kkkkkkkkkk')
        {
            return $this->dispatcher->createInterruption('error', 'error', false);
        }
        $this->partner = PlatSrcDao::model()->findOneByWhere(['src_code' => $this->inputDataBox->getStringNotNull('partner')]);

        $order_id     = $this->inputDataBox->getStringNotNull('order_id');
        $order_title  = $this->inputDataBox->getStringNotNull('order_title');
        $order_detail = $this->inputDataBox->getStringNotNull('order_detail');
        $order_sum    = $this->inputDataBox->getIntNotNull('order_sum');
        $device_ip    = $this->inputDataBox->getStringNotNull('ip');

        $notify_url  = $this->inputDataBox->getStringNotNull('notify_url');
        $return_url  = $this->inputDataBox->tryGetString('return_url');
        $pay_channel = $this->inputDataBox->getStringNotNull('pay_channel');

        $new_order               = new PlatOrderDao();
        $new_order->order_src    = $this->partner->src_code;
        $new_order->src_order_id = $order_id;
        $new_order->title        = $order_title;
        $new_order->detail       = $order_detail;
        $new_order->order_sum    = $order_sum;
        $new_order->device_ip    = $device_ip;
        $new_order->notify_url   = $notify_url;
        $new_order->return_url   = $return_url;
        $new_order->pay_channel  = $pay_channel;
        try
        {
            $new_order->saveAndGenerateOrderOpenId();
        } catch (\Exception $e)
        {
            if (PlatOrderDao::model()->findOneByWhere(['order_src' => $this->partner->src_code, 'src_order_id' => $order_id], false))
            {
                return $this->dispatcher->createInterruption('partner_order_has_exist', '订单已经存在' . $e->getMessage(), false);
            }
            else
            {
                return $this->dispatcher->createInterruption('create_order_fail', '创建订单失败，请稍后重试', false);
            }
        }


        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapaySdk/init.php';
        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapayDemo/config.php';

        $payment = new \AdaPaySdk\Payment();

        $payment_params = array(
            'app_id'      => 'app_4721a7da-805e-47d6-94dc-e5404bd5f19b',
            'order_no'    => $new_order->open_order_id,
            'pay_channel' => $new_order->pay_channel,
            //'time_expire'=> date("YmdHis", time()+86400),
            'pay_amt'     => number_format(floatval($new_order->order_sum) / 100.00, 2),
            'goods_title' => $new_order->title,
            'goods_desc'  => $new_order->detail,
            'description' => $new_order->detail,
            'device_info' => ['device_p' => "111.121.9.10"],
            'notify_url'  => Sys::app()->params['pay']['pay_page_domain'] . '/bee_invasion/v1/game/economy/plat/adapayNotify',
        );

        Sys::app()->setForceLog()->addLog(['request' => $payment_params, 'open_info' => $new_order->getOuterDataArray()], 'OEPN_ORDER');
        try
        {
            # 发起支付
            $payment->create($payment_params);

            # 对支付结果进行处理
            if ($payment->isError())
            {
                //失败处理
                // var_dump($payment->result);
            }
            else
            {
                //成功处理
                //var_dump($payment->result);
            }
        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('create_order_fail', '创建订单失败，请稍后重试1', false);
        }

        if (!(isset($payment->result['expend']['pay_info']) && isset($payment->result['id']) && isset($payment->result['query_url'])))
        {
            return $this->dispatcher->createInterruption('create_order_fail', '创建订单失败，请稍后重试 2', false);
        }

        try
        {
            $new_order->plat_order_id  = $payment->result['id'];
            $new_order->plat_query_url = $payment->result['query_url'];
            $new_order->plat_pay_url   = $payment->result['expend']['pay_info'];
            $new_order->update();
        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('create_order_fail', '创建订单失败，请稍后重试3', false);
        }
        $new_order->cacheOpenInfo();
        return $new_order->getOpenInfo();
    }
}
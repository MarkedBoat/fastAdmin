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
use modules\bee_invasion\v1\model\cache\ApiCache;


class ActionInnerQueryOrder extends ActionBase
{
    public function run()
    {

        $true_order_id = $this->inputDataBox->getStringNotNull('true_order_id');


        try
        {
            $order = PlatOrderDao::model()->findByPk($true_order_id);
        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('order_not_found', '查找不到订单', false);
        }


        try
        {
            $res = $order->syncRemoteOrderInfo();
            if ($res === false)
            {
                return $this->dispatcher->createInterruption('get_order_info_fail', '查找不到订单', false);
            }
            if ($order->is_notifyed === Opt::isNotOk)
            {
                if ($order->is_payed === Opt::isOk)
                {
                    Sys::app()->redis('pay')->rPush(Sys::app()->params['pay']['plat_order_notify_queue'], json_encode($order->getOuterDataArray()));
                    ApiCache::model()->setCache('AdapayOrderOpenInfo', ['open_order_id' => $order->open_order_id], $order->getOpenInfo());
                }
            }

        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('get_order_info_fail', '查找不到订单', false);
        }

        return $order->getOpenInfo();
    }
}
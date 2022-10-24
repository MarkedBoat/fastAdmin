<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;
use modules\bee_invasion\v1\model\cache\ApiCache;


class ActionAdapayNotify extends ActionBase
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'TEXT';

    public function run()
    {
        // Sys::app()->setDebug(false);
        if ($this->rawPostData)
        {
            $strs = explode('&', $this->rawPostData);

            foreach ($strs as $str)
            {
                if (substr($str, 0, 5) === 'data=')
                {
                    $data = json_decode(urldecode(substr($str, 5)), true);

                    if (is_array($data) && isset($data['id']) && isset($data['order_no']))
                    {
                        $true_id = PlatOrderDao::openOrderId2TrueOrderId($data['order_no']);
                        $order   = PlatOrderDao::model()->findByPk($true_id);
                        if (strval($order->plat_order_id) === strval($data['id']))
                        {
                            if ($order->is_payed === Opt::isNotOk || $order->is_refund === Opt::isNotOk)
                            {
                                $order->syncRemoteOrderInfo();
                                if ($order->is_payed === Opt::isOk)
                                {
                                    Sys::app()->redis('pay')->rPush(Sys::app()->params['pay']['plat_order_notify_queue'], json_encode($order->getOuterDataArray()));
                                    ApiCache::model()->setCache('AdapayOrderOpenInfo', ['open_order_id' => $order->open_order_id], $order->getOpenInfo());
                                }
                            }
                            else
                            {
                                // Sys::app()->redis('pay')->rPush(Sys::app()->params['pay']['plat_order_notify_queue'], json_encode($order->getOuterDataArray()));
                            }
                        }
                        //return $data;
                    }
                    break;
                }
            }
        }
        return 'ok';

    }
}
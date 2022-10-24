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


class ActionQQuery extends ActionBase
{
    public $requestMethods = ['GET'];

    public function run()
    {
        Sys::app()->setDebug(false);
        $order_id   = $this->inputDataBox->getStringNotNull('order_id');
        $sign       = $this->inputDataBox->getStringNotNull('sign');
        $plat_order = new PlatOrderDao();
        if ($plat_order->getQuickQuerySign($order_id) !== $sign)
        {
            return $this->dispatcher->createInterruption(AdvError::request_sign_error['detail'], '查找不到订单', false);
        }


        $cache_data = ApiCache::model()->getCache('AdapayOrderOpenInfo', ['open_order_id' => $order_id], false);
        if (empty($cache_data))
        {
            return $this->dispatcher->createInterruption(AdvError::data_not_exist['detail'], '查找不到数据', false);
        }

        return json_decode($cache_data, true);
    }
}
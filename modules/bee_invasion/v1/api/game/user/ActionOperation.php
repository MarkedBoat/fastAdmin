<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Perk;


class ActionOperation extends GameBaseAction
{


    /**
     * @return array
     * @throws \Exception
     */
    public function run()
    {

        $item_class = $this->inputDataBox->getStringNotNull('item_class');
        $item_code  = $this->inputDataBox->getStringNotNull('item_code');

        if ($item_class === 'cg')
        {
            $item = (new ConsumableGoods())->getItemByCode($item_code);
        }
        else if ($item_class === 'perk')
        {
            $item = (new Perk())->getItemByCode($item_code);
        }
        else
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_error['detail'], '请查询正确的类型', false, false);

        }

        $limit_times = 0;
        $used_times  = [];
        $is_ok       = true;
        $fast_cd_sec = 0;
        if (isset($item->threshold['limit']) && isset($item->threshold['limit']['cd']) && is_array($item->threshold['limit']['cd']) && count($item->threshold['limit']['cd']) === 2 && isset($item->threshold['limit']['times']) && is_int($item->threshold['limit']['times']))
        {
            if ($item->threshold['limit']['times'] > 0 && $item->threshold['limit']['cd'][1] === 'sec')
            {
                $limit_times = $item->threshold['limit']['times'];
                $limit       = ApiCache::model()->setOperation($this->user->id, $item_code, $item->threshold['limit']['cd'][0], $item->threshold['limit']['times']);
                $is_ok       = $limit->getStatisInfo();
                $used_times  = $limit->getExistTimeAnchors();
                $fast_cd_sec = $limit->getFastCdTime();
            }
            else
            {
                Sys::app()->addLog($item->threshold, '不计限制');
            }
        }
        else
        {
            Sys::app()->addLog($item->threshold, 'error_config 配置有问题');
        }

        return [
            'limit' => [
                'times'        => $limit_times,
                'used'         => count($used_times),
                'anchors'      => $used_times,
                'fast_expires' => $fast_cd_sec,
                'is_used_up'   => $limit_times <= count($used_times),
                'is_ok'        => $is_ok,
                'item_code'    => $item_code,
                'item_class'   => $item_class,
            ],
        ];

    }
}
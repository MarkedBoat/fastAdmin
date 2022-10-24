<?php

namespace modules\bee_invasion\v1\api\game\role\perk;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RolePorcessDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\role\RolePerk;
use modules\bee_invasion\v1\model\role\RolePerkHis;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCg;


class ActionUsed extends GameBaseAction
{

    /**
     * @return array
     * @throws AdvError
     */
    public function run()
    {

        $item_code = $this->inputDataBox->getStringNotNull('item_code');
        $item      = (new Perk())->getItemByCode($item_code);
        $res       = RolePerkHis::model()->setUser($this->user)->setOperation(RolePerkHis::srcUsed, time())->setItem($item_code, 1)->recordHis();
        if ($res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录技能失败', false, false);
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
                $is_ok       = $limit->tryRecord(true);
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
            'user'  => [
                'goods' => [
                    $item_code => RolePerk::getGoodsAmount($this->user, $item_code)
                ]
            ],
            'limit' => [
                'times'        => $limit_times,
                'used'         => count($used_times),
                'anchors'      => $used_times,
                'fast_expires' => $fast_cd_sec,
                'is_used_up'   => $limit_times <= count($used_times),
                'is_ok'        => $is_ok,
                'item_code'    => $item_code,
                'item_class'   => 'perk',
            ],
        ];

    }
}
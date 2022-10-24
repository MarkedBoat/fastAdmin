<?php

namespace modules\bee_invasion\v1\api\game\role\cg;

use models\Api;
use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
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
        //$this->dispatcher->setOutType(Api::outTypeText);

        $item_code    = $this->inputDataBox->getStringNotNull('item_code');
        $item         = (new ConsumableGoods())->getItemByCode($item_code);
        $user_account = UserCg::model()->setUser($this->user)->getAccount($item_code);
        $his          = UserCgHis::model()->setUserAccountModel($user_account);
        $item_value   = 1 * pow(10, 8);
        $res          = $his->tryRecord(UserCgHis::srcUsed, time(), $item_value);
        if ($res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录道具使用失败', false, false);
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
            'user'            => [
                'cg' => [
                    $item_code => $user_account->getAccount(false, false)->item_amount
                ]
            ],
            'limit'           => [
                'times'        => $limit_times,
                'used'         => count($used_times),
                'anchors'      => $used_times,
                'fast_expires' => $fast_cd_sec,
                'is_used_up'   => $limit_times <= count($used_times),
                'is_ok'        => $is_ok,
                'item_code'    => $item_code,
                'item_class'   => 'cg',
            ],
            'userInfoChanged' => $this->user->getChangedCodes(),
        ];

    }
}
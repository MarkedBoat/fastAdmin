<?php

namespace modules\bee_invasion\v1\api\game\user\object;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserObjectHis;


class ActionFillPostalInfo extends GameBaseAction
{
    public function run()
    {
        $his_id    = $this->inputDataBox->getIntNotNull('item_id');
        $area_code = $this->inputDataBox->getIntNotNull('area_code');
        $area_name = $this->inputDataBox->getStringNotNull('area_name');
        $addr      = $this->inputDataBox->getStringNotNull('address');
        $user_tel  = $this->inputDataBox->getIntNotNull('user_tel');
        $user_name = $this->inputDataBox->getStringNotNull('user_name');

        if (strlen($area_code) !== 6 && strlen($user_tel) !== 11)
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], AdvError::request_param_verify_fail['msg'], false);

        }

        $his_dao = UserObjectHis::model()->findByPk($his_id, false);
        if (empty($his_dao))
        {
            return $this->dispatcher->createInterruption(AdvError::res_not_exist['detail'], '信息不存在', false);
        }
        if ($his_dao->user_id !== $this->user->id)
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], '信息不存在', false);
        }
        if ($his_dao->src_op_step !== UserObjectHis::stepPostalInfo)
        {
            return $this->dispatcher->createInterruption(AdvError::res_has_delivered['detail'], '已经填写过收获信息了，如需要修改请联系客服', false);
        }

        $his_dao->src_op_step = UserObjectHis::stepWaitInfoCheck;
        $his_dao->user_name   = $user_name;
        $his_dao->user_tel    = $user_tel;
        $his_dao->user_addr   = "{$area_code}\n{$area_name}\n{$addr}";
        $his_dao->update(false);
        $his_dao->reloadData();

        return ['info' => $his_dao->getOpenInfo()];

    }
}
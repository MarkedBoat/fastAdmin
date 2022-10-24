<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\user\User;


class ActionBindInviter extends GameBaseAction
{
    public function run()
    {
        $dao = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $this->user->id], false);
        if (!empty($dao) && $dao->inviter_id)
        {
            return $this->dispatcher->createInterruption(AdvError::data_info_unexpected['detail'], '你已经绑定过了邀请人', false);
        }
        $tel       = $this->inputDataBox->tryGetInt('tel');
        $open_id   = $this->inputDataBox->tryGetInt('open_id');
        $open_code = $this->inputDataBox->tryGetString('open_code');

        if ($open_code)
        {
            $user = User::model()->findOneByWhere(['id' => User::openCode2TrueId($open_code)], false);
            Sys::app()->addLog($open_code, '$open_code');
        }
        else if ($open_id)
        {
            $user = User::model()->findOneByWhere(['id' => User::openId2TrueId($open_id)], false);
        }
        else if ($tel)
        {
            $user = User::model()->findOneByWhere(['mobile' => $tel], false);
        }
        else
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], AdvError::request_param_verify_fail['msg'], false);
        }

        if (empty($user) || ($user->id === $this->user->id))
        {
            return $this->dispatcher->createInterruption(AdvError::res_not_exist['detail'], '邀请人用户不存在', false);
        }
        $dao                = UserInviterDao::model();
        $dao->be_invited_id = $this->user->id;
        $dao->inviter_id    = $user->id;
        $res                = $dao->insert(false);
        if ($res == false)
        {
            return $this->dispatcher->createInterruption(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'], ['res' => false]);
        }
        return ['res' => true];
    }

}
<?php

namespace modules\bee_invasion\v1\api\open\user;

use models\common\ActionBase;
use models\common\error\AdvError;
use modules\bee_invasion\v1\model\user\User;


class ActionSimpleInfo extends ActionBase
{
    // public $requestMethods = 'POST';
    public $dataSource = 'JSON_STRING';

    public function run()
    {

        $tel     = $this->inputDataBox->tryGetInt('tel');
        $open_id = $this->inputDataBox->tryGetInt('open_id');

        if ($open_id)
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

        if (empty($user))
        {
            return $this->dispatcher->createInterruption(AdvError::res_not_exist['code'], '用户不存在', false);
        }
        return ['user' => $user->getSimpleOpenInfo()];
    }

}
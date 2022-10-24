<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\error\AdvError;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\user\User;


class ActionGetInviter extends GameBaseAction
{
    public function run()
    {
        $dao = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $this->user->id], false);
        if (empty($dao) || !$dao->inviter_id)
        {
            return $this->dispatcher->createInterruption('user_has_not_inviter', '您尚未绑定过邀请人', ['inviter' => false]);
        }
        $user = User::model()->findOneByWhere(['id' => $dao->inviter_id], false);
        if (empty($user))
        {
            return $this->dispatcher->createInterruption(AdvError::data_info_unexpected['detail'], '邀请人信息异常', ['inviter' => false]);

        }
        return ['inviter' => $user->getSimpleOpenInfo()];
    }

}
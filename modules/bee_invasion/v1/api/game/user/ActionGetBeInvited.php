<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionGetBeInvited extends GameBaseAction
{
    public function run()
    {
        //$page_size  = $this->inputDataBox->getIntNotNull('page_size');
        $page_index = $this->inputDataBox->getIntNotNull('page_index');
        $page_size  = 20;
        $conditions = ['inviter_id' => $this->user->id];

        $dao = UserInviterDao::model()->addSort('id', 'desc');
        if ($page_size && $page_index)
        {
            $dao->setPage($page_index, $page_size)->setOptCountTotalStatus(true);
        }
        $daos        = $dao->findAllByWhere($conditions, false);
        $page_info   = $dao->getPageInfo();
        $list        = [];
        $user_id_map = [];
        $user_ids    = [];
        foreach ($daos as $i => $dao)
        {
            $user_id_map[$dao->be_invited_id] = $i;
            $user_ids[]                       = $dao->be_invited_id;
            $list[$i]                         = [
                'inviter_id'    => $dao->inviter_id,
                'be_invited_id' => $dao->be_invited_id,
                'create_time'   => $dao->create_time,
            ];

        }
        if (count($user_ids))
        {
            $user_models = User::model()->findAllByWhere(['id' => $user_ids]);
            foreach ($user_models as $user_model)
            {
                $list[$user_id_map[$user_model->id]]['be_invited_user'] = $user_model->getOpenInfo();
            }
        }

        $page_info['list'] = $list;
        return $page_info;
    }

}
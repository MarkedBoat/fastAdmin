<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionRoleUserList extends AdminBaseAction
{
    public function run()
    {
        $data['id'] = $this->inputDataBox->getStringNotNull('id');

        return Sys::app()->db('dev')->setText("select r_u.id,r.role_name,t.real_name,'user_role' as tname from dp_bg_admin t left join dp_bg_rbac_user_role r_u on t.id = r_u.user_id left join  dp_bg_rbac_role r on r.id = r_u.role_id where r.id = :id and t.is_ok = 1 and r_u.is_ok = 1 and r.is_ok = 1")->bindArray($data)->queryAll();
    }

}
<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;

class ActionUserRoleList extends AdminBaseAction
{
    public function run()
    {
        $data['id'] = $this->inputDataBox->getStringNotNull('id');//用户id

        return Sys::app()->db('dp')->setText("select r_u.id,r.role_name,t.real_name,'user_role' as tname from bg_admin t left join bg_rbac_user_role r_u on t.id = r_u.user_id left join  bg_rbac_role r on r.id = r_u.role_id where t.id = :id and t.is_ok = 1 and r_u.is_ok = 1 and r.is_ok = 1")->bindArray($data)->queryAll();
    }

}
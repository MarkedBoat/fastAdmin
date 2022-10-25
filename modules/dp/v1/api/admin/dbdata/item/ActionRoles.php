<?php

namespace modules\bee_invasion\v1\api\admin\dbdata\item;

use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\rbac\RbacRole;

class ActionRoles extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;
        @header('content-Type:application/json;charset=utf8');


        //        $where = 'is_ok = :is_ok';
        //        $name = $this->inputDataBox->tryGetString('keyword');
        //        if($name){
        //            $where .= " and (LOCATE(:keyword,role_name) != 0 or id = :keyword or LOCATE(:keyword,role_code) != 0)";
        //            $data['keyword'] = $name;
        //        }
        //        return Sys::app()->db('dev')->setText("select *,'role' as tname from dp_bg_rbac_role where ".$where)->bindArray($data)->queryAll();
        //        //return RbacRoleDao::model()->findAllByWhere(['is_ok' => Opt::isOk]);
        $roles = RbacRole::model()->findAllByWhere(['is_ok' => Opt::isOk]);
        $items = [];
        foreach ($roles as $role)
        {
            $items[] = ['text' => $role->role_name, 'val' => $role->role_code];
        }
        die(json_encode($items));
    }


}
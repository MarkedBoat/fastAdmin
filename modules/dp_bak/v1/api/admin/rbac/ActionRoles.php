<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;

class ActionRoles extends AdminBaseAction
{
    public function run()
    {
        $data['is_ok'] = Opt::isOk;
        $where = 'is_ok = :is_ok';
        $name = $this->inputDataBox->tryGetString('keyword');
        if($name){
            $where .= " and (LOCATE(:keyword,role_name) != 0 or id = :keyword or LOCATE(:keyword,role_code) != 0)";
            $data['keyword'] = $name;
        }
        return Sys::app()->db('dp')->setText("select *,'role' as tname from bg_rbac_role where ".$where)->bindArray($data)->queryAll();
        //return RbacRoleDao::model()->findAllByWhere(['is_ok' => Opt::isOk]);
    }


}
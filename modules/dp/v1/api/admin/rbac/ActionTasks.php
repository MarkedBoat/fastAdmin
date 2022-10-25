<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleTaskDao;

class ActionTasks extends AdminBaseAction
{
    public function run()
    {
        $data['is_ok'] = Opt::isOk;
        $where = 'is_ok = :is_ok';
        $keyword = $this->inputDataBox->tryGetString('keyword');
        if($keyword){
            $where .= " and (LOCATE(:keyword,task_name) != 0 or id = :keyword or LOCATE(:keyword,task_code) != 0)";
            $data['keyword'] = $keyword;
        }
        return Sys::app()->db('dev')->setText("select *,'task' as tname from dp_bg_rbac_task where ".$where)->bindArray($data)->queryAll();
        //return RbacRoleTaskDao::model()->findAllByWhere(['is_ok' => Opt::isOk]);
    }


}
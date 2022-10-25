<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\rbac\RbacRoleTask;

class ActionRoleBindTask extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $RbacRoleTask                  = new RbacRoleTask();

        if($id){
            $RbacRoleTask->id    = $id;
            $RbacRoleTask = RbacRoleTask::model()->findByPk($id, false);
            if(!$RbacRoleTask){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            $RbacRoleTask->is_ok    = $type;

            $res = $RbacRoleTask->update();
        }else{
            $role_id = $this->inputDataBox->getStringNotNull('role_id');
            $task_id = $this->inputDataBox->getStringNotNull('task_id');
            $RbacRoleTask->role_id    = $role_id;
            $RbacRoleTask->task_id = $task_id;
            $res = $RbacRoleTask->insert(false);

        }
        if(!$res){
            throw new \Exception('处理失败');
        }

        return 1;
    }


}
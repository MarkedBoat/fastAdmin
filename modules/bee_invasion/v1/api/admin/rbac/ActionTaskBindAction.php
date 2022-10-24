<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacTaskAction;

class ActionTaskBindAction extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $RbacTaskAction                  = new RbacTaskAction();

        if($id){
            $RbacTaskAction->id    = $id;
            $RbacTaskAction = RbacTaskAction::model()->findByPk($id, false);
            if(!$RbacTaskAction){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            //禁用
            $RbacTaskAction->is_ok    = $type;

            $res = $RbacTaskAction->update();
        }else{
            $action_id = $this->inputDataBox->getStringNotNull('action_id');
            $task_id = $this->inputDataBox->getStringNotNull('task_id');
            $RbacTaskAction->action_id    = $action_id;
            $RbacTaskAction->task_id = $task_id;
            $res = $RbacTaskAction->insert(false);

        }
        if(!$res){
            throw new \Exception('处理失败');
        }

        return 1;
    }


}
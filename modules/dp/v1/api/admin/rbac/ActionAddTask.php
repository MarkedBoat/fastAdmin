<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacTask;


class ActionAddTask extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $RbacModel                  = new RbacTask();

        if($id){
            $RbacModel->id    = $id;
            $RbacModel = RbacTask::model()->findByPk($id, false);
            if(!$RbacModel){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            $RbacModel->is_ok    = $type;

            $name = $this->inputDataBox->tryGetString('name');
            if($name){
                $RbacModel->task_name    = $name;
            }
            $code = $this->inputDataBox->tryGetString('code');
            if($code){
                $RbacModel->task_code =$code;
            }
            $res = $RbacModel->update();
        }else{
            $name = $this->inputDataBox->getStringNotNull('name');
            $code = $this->inputDataBox->getStringNotNull('code');
            $RbacModel->task_name    = $name;
            $RbacModel->task_code = $code;
            $res = $RbacModel->insert(false);
        }
        if(!$res){
            throw new \Exception('添加失败');
        }

        return 1;
    }


}
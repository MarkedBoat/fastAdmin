<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;


class ActionAddAction extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $RbacAction                  = new RbacAction();

        if($id){
            $RbacAction->id    = $id;
            $RbacAction = RbacAction::model()->findByPk($id, false);
            if(!$RbacAction){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            if($type){
                //禁用
                $RbacAction->is_ok    = $type;
            }
            $action_name = $this->inputDataBox->tryGetString('action_name');
            if($action_name){
                $RbacAction->action_name    = $action_name;
            }
            $action_uri = $this->inputDataBox->tryGetString('action_uri');
            if($action_uri){
                $RbacAction->action_uri = $action_uri;
                $action_code = str_replace('/','_',$action_uri);
                $RbacAction->action_code =$action_code;
            }
            $res = $RbacAction->update();
        }else{
            $action_name = $this->inputDataBox->getStringNotNull('action_name');
            $action_uri = $this->inputDataBox->getStringNotNull('action_uri');
            $RbacAction->action_name    = $action_name;
            $RbacAction->action_uri = $action_uri;
            $action_code = str_replace('/','_',$action_uri);
            $RbacAction->action_code =$action_code;
            $res = $RbacAction->insert(false);
            //        $data = [
            //          'action_name'=>$action_name,
            //          'action_uri' => $action_uri,
            //          'action_code' => $action_code
            //        ];
            //        $where = '(action_name,action_uri,action_code) values (:action_name,:action_uri,:action_code)';
            //        $insert_res = Sys::app()->db('dev')->setText("insert into dp_bg_rbac_action" . $where)->bindArray($data)->execute();

        }
        if(!$res){
            throw new \Exception('添加失败');
        }

        return 1;
    }


}
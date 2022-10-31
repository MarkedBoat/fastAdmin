<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\model\admin\rbac\RbacMenu;


class ActionAddMenu extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id')??0;
        $RbacMenu                  = new RbacMenu();

        if($id){
            $RbacMenu->id    = $id;
            $RbacMenu = RbacMenu::model()->findByPk($id, false);
            if(!$RbacMenu){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            if($type){
                //禁用
                $RbacMenu->is_ok    = $type;
            }
            $title = $this->inputDataBox->tryGetString('title');
            if($title){
                $RbacMenu->title    = $title;
            }
            $url = $this->inputDataBox->tryGetString('url');
            if($url){
                $RbacMenu->url = $url;
            }
            $pid = $this->inputDataBox->tryGetString('pid');
            if($pid){
                $RbacMenu->pid = $pid;
            }
            $remark = $this->inputDataBox->tryGetString('remark');
            if($remark){
                $RbacMenu->remark = $remark;
            }
            $opt = $this->inputDataBox->tryGetString('opt');
            if($opt){
                $RbacMenu->opts = $opt;
            }

            $res = $RbacMenu->update();
        }else{
            $title = $this->inputDataBox->getStringNotNull('title');
            $url = $this->inputDataBox->getStringNotNull('url');
            $pid = $this->inputDataBox->getStringNotNull('pid');
            $remark = $this->inputDataBox->getStringNotNull('remark');
            $opt = $this->inputDataBox->getStringNotNull('opt');
            $RbacMenu->title    = $title;
            $RbacMenu->url = $url;
            $RbacMenu->pid =$pid;
            $RbacMenu->remark =$remark;
            $RbacMenu->opts =$opt;
            $res = $RbacMenu->insert(false);

        }
        if(!$res){
            throw new \Exception('添加失败');
        }

        return 1;
    }


}
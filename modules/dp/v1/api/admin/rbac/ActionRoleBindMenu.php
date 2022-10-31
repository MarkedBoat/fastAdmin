<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\rbac\RbacMenu;
use modules\dp\v1\model\admin\rbac\RbacRoleMenu;

class ActionRoleBindMenu extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $RbacRoleMenu                  = new RbacRoleMenu();

        if($id){
            $RbacRoleMenu->id    = $id;
            $RbacRoleMenu = RbacRoleMenu::model()->findByPk($id, false);
            if(!$RbacRoleMenu){
                throw new \Exception('数据不存在');
            }
            //修改或删除禁用
            $type = $this->inputDataBox->tryGetString('type');
            $RbacRoleMenu->is_ok    = $type;
            $role_id = $this->inputDataBox->tryGetString('role_id');
            if($role_id){
                $RbacRoleMenu->role_id    = $role_id;
            }
            $menu_id = $this->inputDataBox->tryGetString('menu_id');
            if($menu_id){
                $RbacRoleMenu->menu_id = $menu_id;
            }
            $res = $RbacRoleMenu->update();
        }else{
            $role_id = $this->inputDataBox->getStringNotNull('role_id');
            $menu_id = $this->inputDataBox->getStringNotNull('menu_id');
            //添加菜单判断是否是子集
            $Menu = RbacMenu::model()->findByPk($menu_id,false);
            if($Menu->pid != 0){
                $RoleMenu = Sys::app()->db('dp')->setText("select * from bg_rbac_role_menu where menu_id = :menu_id and role_id = :role_id")->bindArray(['menu_id'=>$Menu->pid,'role_id'=>$role_id])->queryRow();
                //$RoleMenu = RbacRoleMenu::model()->findOneByWhere(['menu_id'=>$Menu->pid,'role_id'=>$role_id]);
                if(!$RoleMenu){
                    throw new \Exception('请先添加该菜单的父级');
                }
            }
            $RbacRoleMenu->role_id    = $role_id;
            $RbacRoleMenu->menu_id = $menu_id;
            $res = $RbacRoleMenu->insert(false);

        }
        if(!$res){
            throw new \Exception('处理失败');
        }

        return 1;
    }


}
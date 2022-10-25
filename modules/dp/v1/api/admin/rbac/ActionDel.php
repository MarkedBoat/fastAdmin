<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacMenu;
use modules\bee_invasion\v1\model\admin\rbac\RbacRoleMenu;

class ActionDel extends AdminBaseAction
{
    public function run()
    {
        $data['id'] = $this->inputDataBox->getStringNotNull('id');
        $name = $this->inputDataBox->getStringNotNull('name');
        $table = 'dp_bg_rbac_'.$name;
        if($name == 'menu' || $name == 'role_menu'){
            //待修改
            switch ($name){
                case 'menu' :
                    //添加菜单判断是否是父集
                    $Menu = RbacMenu::model()->findByPk($data['id'],false);
                    if($Menu->pid == 0){
                        $RoleMenu = Sys::app()->db('dev')->setText("select * from dp_bg_rbac_menu where pid = :pid")->bindArray(['pid'=>$Menu->id])->queryRow();
                        if($RoleMenu){
                            throw new \Exception('该菜单子级仍在使用，请先删除子集数据');
                        }
                    }
                    break;
                case 'role_menu':
                    //添加菜单判断是否是父集
                    $Menu = RbacRoleMenu::model()->findByPk($data['id'],false);
                    $Menu = RbacMenu::model()->findByPk($Menu->menu_id,false);
                    if($Menu->pid == 0){
                        //是父级获取子集
                        $sonMenu = Sys::app()->db('dev')->setText("select id from dp_bg_rbac_menu where pid = :pid")->bindArray(['pid'=>$Menu->id])->queryAll();
                        if($sonMenu){
                            foreach($sonMenu as $k=>$v){
                                $gid_array[] = $v['id'];
                            }
                            if(count($gid_array)<2){
                                $id_str = $gid_array['0'];
                            }else{
                                $id_str = implode(',',$gid_array);
                            }
                            $RoleMenu = Sys::app()->db('dev')->setText("select * from dp_bg_rbac_role_menu where menu_id in (".$id_str.")")->queryRow();
                            if($RoleMenu){
                                throw new \Exception('该菜单子级仍在使用，请先删除子集数据');
                            }
                        }
                    }
                    break;
            }
            return Sys::app()->db('dev')->setText("delete from ".$table." where id = :id")->bindArray($data)->execute();
        }else{
            return Sys::app()->db('dev')->setText("delete from ".$table." where id = :id")->bindArray($data)->execute();
        }


    }

}
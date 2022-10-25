<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\AdminDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacUserRoleDao;
use modules\bee_invasion\v1\model\admin\rbac\RbacUserRole;

class ActionUserAddRole extends AdminBaseAction
{
    public function run()
    {
        $id = $this->inputDataBox->tryGetString('id');
        $UserRole                  = new RbacUserRoleDao();
        if($id){
            $UserRole = RbacUserRole::model()->findByPk($id,false);
            if(!$UserRole){
                throw new \Exception('该数据不存在');
            }else{
                //删除禁用
                $type = $this->inputDataBox->tryGetString('type');
                //禁用
                $UserRole->is_ok    = $type;
                $res = $UserRole->update();
            }
        }else{
            //$name = $this->inputDataBox->getStringNotNull('name');
            $id = $this->inputDataBox->getStringNotNull('admin_id');
            $adminid = AdminDao::model()->findOneByWhere(['id' => $id]);
            if(!$adminid->id){
                throw new \Exception('该用户不存在');
            }
            $role_id = $this->inputDataBox->getStringNotNull('roleId');
            $UserRole->user_id    = $id;
            $UserRole->role_id = $role_id;
            $res = $UserRole->insert(false);
        }

        if(!$res){
            throw new \Exception('添加失败');
        }
        return 1;
    }


}
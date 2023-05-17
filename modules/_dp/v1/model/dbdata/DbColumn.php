<?php

namespace modules\_dp\v1\model\dbdata;


use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbColumnDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;
use modules\_dp\v1\dao\game\RoleDao;
use modules\_dp\v1\dao\game\RoleLevCfgDao;
use modules\_dp\v1\dao\user\UserCgHisDao;
use modules\_dp\v1\dao\user\UserDao;
use modules\_dp\v1\dao\user\UserInviterDao;
use modules\_dp\v1\model\Admin;
use modules\_dp\v1\model\TCache;
use modules\_dp\v1\model\TInfo;

class DbColumn extends DbColumnDao
{


    public function getOpenInfo()
    {
        return [
            'title'              => $this->title,
            //'dbconf_name'         => $this->dbconf_name,
            // 'table_name'      => $this->table_name,
            'column_name'        => $this->column_name,
            'column_sn'          => $this->column_sn,
            'val_items'          => $this->getJsondecodedValue($this->val_items, 'array'),
            'db_datatype'        => $this->db_datatype,
            'db_datatype_len'    => $this->db_datatype_len,
            'out_datatype'       => $this->out_datatype,
            'in_datatype'        => $this->in_datatype,
            'index_key'          => $this->index_key,
            'default_val'        => $this->default_val,
            'remark'             => $this->remark,
            'accessSelectRoles'  => $this->getJsondecodedValue($this->access_select_role_codes, 'array'),
            'accessUpdateRoles'  => $this->getJsondecodedValue($this->access_update_role_codes, 'array'),
            'columnDefault'      => $this->getJsondecodedValue($this->default_opts, 'object'),
            'query_input_type'   => $this->query_input_type,
            'has_query_datalist' => $this->has_query_datalist,
            'is_query_multi'     => $this->is_query_multi,
            'create_time'        => $this->create_time,
            'update_time'        => $this->update_time,
        ];


    }


    public function checkSelectAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'access_select_role_codes');
    }

    public function checkUpdateAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'access_update_role_codes');
    }


    public function checkAccess($user_roles, $access_field, $empty_as_access = true)
    {
        if (is_null($this->$access_field))
        {
            Sys::app()->addLog("col_conf_check_access_{$access_field} is null");
            return $empty_as_access ? true : false;
        }
        $access_roles = $this->getJsondecodedValue($this->$access_field, 'array');
        if (count($access_roles) === 0)
        {
            Sys::app()->addLog([$access_roles, $this->$access_field], "col_conf_check_access_{$access_field} empty array");
            return $empty_as_access ? true : false;
        }
        $intersect_roles = array_intersect($user_roles, $access_roles);
        if (count($intersect_roles) === 0)
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "col_conf_check_access_fail_{$access_field} array_intersect");
            return false;
        }
        else
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "col_conf_check_access_ok_{$access_field} array_intersect");
            return true;
        }
    }

}
<?php

namespace modules\_dp\v1\model\dbdata;


use models\common\opt\Opt;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbColumnDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;
use modules\_dp\v1\dao\game\RoleDao;
use modules\_dp\v1\dao\game\RoleLevCfgDao;
use modules\_dp\v1\dao\user\UserCgHisDao;
use modules\_dp\v1\dao\user\UserDao;
use modules\_dp\v1\dao\user\UserInviterDao;
use modules\_dp\v1\model\TCache;
use modules\_dp\v1\model\TInfo;

class DbColumn extends DbColumnDao
{


    public function getOpenInfo()
    {
        return [
            'title'           => $this->title,
            //'dbconf_name'         => $this->dbconf_name,
            // 'table_name'      => $this->table_name,
            'column_name'     => $this->column_name,
            'column_sn'       => $this->column_sn,
            'val_items'       => $this->getJsondecodedValue($this->val_items, 'array'),
            'db_datatype'     => $this->db_datatype,
            'db_datatype_len' => $this->db_datatype_len,
            'out_datatype'    => $this->out_datatype,
            'in_datatype'     => $this->in_datatype,
            'index_key'       => $this->index_key,
            'default_val'     => $this->default_val,
            'remark'          => $this->remark,
            'read_roles'      => $this->getJsondecodedValue($this->read_roles, 'array'),
            'update_roles'       => $this->getJsondecodedValue($this->update_roles, 'array'),
            'all_roles'       => $this->getJsondecodedValue($this->all_roles, 'array'),
            'create_time'     => $this->create_time,
            'update_time'     => $this->update_time,
        ];


    }
}
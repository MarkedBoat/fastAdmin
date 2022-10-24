<?php

namespace modules\bee_invasion\v1\model\admin\dbdata;


use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\admin\AdminDao;
use modules\bee_invasion\v1\dao\admin\dbdata\DbColumnDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacActionDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;

class DbColumn extends DbColumnDao
{
    use TInfo;

    public function getOpenInfo()
    {
        return [
            'title'           => $this->title,
            //'db_name'         => $this->db_name,
            // 'table_name'      => $this->table_name,
            'column_name'     => $this->column_name,
            'column_sn'       => $this->column_sn,
            'val_range'       => $this->getJsondecodedValue($this->val_range, 'array'),
            'db_datatype'     => $this->db_datatype,
            'db_datatype_len' => $this->db_datatype_len,
            'out_datatype'    => $this->out_datatype,
            'in_datatype'     => $this->in_datatype,
            'index_key'       => $this->index_key,
            'default_val'     => $this->default_val,
            'remark'          => $this->remark,
            'read_roles'      => $this->getJsondecodedValue($this->read_roles, 'array'),
            'opt_roles'       => $this->getJsondecodedValue($this->opt_roles, 'array'),
            'add_roles'       => $this->getJsondecodedValue($this->add_roles, 'array'),
            'create_time'     => $this->create_time,
            'update_time'     => $this->update_time,
        ];


    }
}
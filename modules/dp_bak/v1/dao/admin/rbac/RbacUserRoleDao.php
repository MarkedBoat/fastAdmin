<?php

namespace modules\dp\v1\dao\admin\rbac;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id
 * @property int role_id
 * @property string create_time
 * @property string update_time
 * @property int is_ok
 */
class RbacUserRoleDao extends ORM
{
    public $id          = null;
    public $user_id     = null;
    public $role_id     = null;
    public $create_time = null;
    public $update_time = null;
    public $is_ok       = 1;


    public static $_fields_str;
    public static $tableName    = 'bg_rbac_user_role';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'role_id'     => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
    ];

    public function getDbConfName()
    {
        return 'dp';
    }

}
<?php

namespace modules\bee_invasion\v1\dao\admin\rbac;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string task_name
 * @property string task_code
 * @property string create_time
 * @property string update_time
 * @property int is_ok
 */
class RbacTaskDao extends ORM
{
    public $id          = null;
    public $task_name   = null;
    public $task_code   = null;
    public $create_time = null;
    public $update_time = null;
    public $is_ok       = 1;


    public static $_fields_str;
    public static $tableName    = 'dp_bg_rbac_task';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'task_name'   => ['db_type' => 'varchar', 'length' => 45, 'def' => null, 'pro_def' => null],
        'task_code'   => ['db_type' => 'varchar', 'length' => 45, 'def' => null, 'pro_def' => null],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
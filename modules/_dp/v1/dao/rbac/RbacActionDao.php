<?php

namespace modules\_dp\v1\dao\rbac;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string action_name
 * @property string action_code
 * @property string action_uri
 * @property string create_time
 * @property string update_time
 * @property int is_ok
 */
class RbacActionDao extends ORM
{
    public $id          = null;
    public $action_name = null;
    public $action_code = null;
    public $action_uri  = null;
    public $create_time = null;
    public $update_time = null;
    public $is_ok       = 1;


    public static $_fields_str;
    public static $tableName    = 'bg_rbac_action';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'action_name' => ['db_type' => 'varchar', 'length' => 45, 'def' => null, 'pro_def' => null],
        'action_code' => ['db_type' => 'varchar', 'length' => 45, 'def' => null, 'pro_def' => null],
        'action_uri'  => ['db_type' => 'varchar', 'length' => 128, 'def' => null, 'pro_def' => null],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
    ];


    public function getDbConfName()
    {
        return 'fast_bg';
    }

}
<?php

namespace modules\bee_invasion\v1\dao\admin;

use models\common\db\ORM;

/**
 * @property int id
 * @property string real_name 用户姓名
 * @property string password 用户密码
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class AdminDao extends ORM
{
    public $id          = null;
    public $real_name   = '';
    public $password    = '';
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'dp_bg_admin';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'real_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'password'    => ['db_type' => 'char', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
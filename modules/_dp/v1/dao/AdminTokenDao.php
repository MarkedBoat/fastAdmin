<?php

namespace modules\_dp\v1\dao;

use models\common\db\ORM;

/**
 * @property int id
 * @property int user_id 用户id
 * @property string user_token
 * @property int expires 过期时间
 * @property string login_info 扩展参数
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class AdminTokenDao extends ORM
{
    public $id          = null;
    public $user_id     = 0;
    public $user_token  = '';
    public $expires     = 0;
    public $login_info  = '';
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bg_admin_token';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_token'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'expires'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'login_info'  => ['db_type' => 'varchar', 'length' => 256, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'fast_bg';
    }

}
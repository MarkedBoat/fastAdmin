<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id 用户id user.id
 * @property string utk 用户token
 * @property string detail 描述
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string reg_time
 * @property string create_time
 * @property string update_time
 */
class UserFakeDao extends ORM
{
    public $id          = null;
    public $utk         = '';
    public $detail      = '';
    public $is_ok       = 1;
    public $reg_time    = null;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_fake';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'utk'         => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'detail'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'reg_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
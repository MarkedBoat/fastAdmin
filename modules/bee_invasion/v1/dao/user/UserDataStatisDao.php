<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id 用户id
 * @property string item_code 统计code
 * @property string item_value 记录值，最后的
 * @property int item_addup 累计值
 * @property int item_max 最大值
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class UserDataStatisDao extends ORM
{
    public $id          = null;
    public $user_id     = 0;
    public $item_code   = '';
    public $item_value  = '';
    public $item_addup  = 0;
    public $item_max    = 0;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_data_statis';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_value'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_addup'  => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_max'    => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
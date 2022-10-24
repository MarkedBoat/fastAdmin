<?php

namespace modules\bee_invasion\v1\dao\game\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id 用户id
 * @property string item_code 票据  code ，冗余
 * @property int item_amount 通货数量
 * @property string item_value 当前的值
 * @property int item_expires
 * @property int item_status 是否正常  1:未使用  2:使用者
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class RoleNoteDao extends ORM
{
    public $id           = null;
    public $user_id      = 0;
    public $item_code    = 0;
    public $item_amount  = 0;
    public $item_value   = 0;
    public $item_expires = 0;
    public $item_status  = 1;
    public $is_ok        = 1;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_note';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_amount'  => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_value'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_expires' => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_status'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_ok'        => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
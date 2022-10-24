<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\ORM;

/**
 * @property int id
 * @property int user_id 用户id
 * @property string partner_code 伙伴 code
 * @property string open_code 对外展示的 code
 * @property int is_used 是否正常  1:使用过了  2:未使用
 * @property string create_time
 * @property string update_time
 */
class OpenCodeDao extends ORM
{
    public $id           = null;
    public $user_id      = 0;
    public $partner_code = '';
    public $open_code    = '';
    public $is_used      = 2;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_open_code';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'partner_code' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'open_code'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'is_used'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'create_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
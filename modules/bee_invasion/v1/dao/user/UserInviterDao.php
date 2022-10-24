<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int inviter_id 通货归类
 * @property int be_invited_id 通货归类
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class UserInviterDao extends ORM
{
    public $id            = null;
    public $inviter_id    = 0;
    public $be_invited_id = 0;
    public $is_ok         = 1;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_inviter';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'inviter_id'    => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'be_invited_id' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
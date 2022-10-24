<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id 用户id
 * @property int lev 角色等级
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class RoleDao extends ORM
{
    public $id          = null;
    public $user_id     = 0;
    public $lev         = 1;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'lev'         => ['db_type' => 'smallint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'      => intval($this->id),
            'user_id' => intval($this->user_id),
            'lev'     => intval($this->lev),
        ];
    }


}
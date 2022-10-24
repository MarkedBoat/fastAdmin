<?php

namespace modules\bee_invasion\v1\dao\game\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int role_id 用户id
 * @property string perk_item_code 技能/装备code
 * @property int used_times 技能/装备 id
 * @property int is_active 是否正常  1:yes  2:no
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class RolePerkDao extends ORM
{
    public $id             = null;
    public $role_id        = 0;
    public $perk_item_code = 0;
    public $used_times     = 0;
    public $is_active      = 1;
    public $is_ok          = 1;
    public $create_time    = null;
    public $update_time    = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_perk';
    public static $pk           = 'id';
    public static $field_config = [
        'id'             => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'role_id'        => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'perk_item_code' => ['db_type' => 'varchar', 'length' => 64, 'def' => '0', 'pro_def' => 0],
        'used_times'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_active'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_ok'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
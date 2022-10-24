<?php

namespace modules\bee_invasion\v1\dao\game\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id role id
 * @property int stage_index 关卡 id
 * @property string create_time
 * @property string update_time
 */
class RolePorcessDao extends ORM
{
    public $id          = 0;
    public $stage_index = 0;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_process';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'stage_index' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
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
            'id'          => intval($this->id),
            'stage_index' => intval($this->stage_index),
        ];
    }


}
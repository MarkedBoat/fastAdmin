<?php

namespace modules\bee_invasion\v1\dao\game\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id role id
 * @property string armed 装备信息，装备的什么东西  armor,weapon
 * @property string create_time
 * @property string update_time
 */
class RoleArmDao extends ORM
{
    public $id          = 0;
    public $armed       = null;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_arm';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'armed'       => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
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
            'id'    => intval($this->id),
            'armed' => $this->getJsondecodedValue($this->armed, 'object'),
        ];
    }


}
<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int lev 等级
 * @property string lev_title 等级名称
 * @property int base_hp 生命值
 * @property int base_dmg 伤害值
 * @property int lev_up_points 升级积分
 * @property string award 升级后的奖励
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class RoleLevCfgDao extends ORM
{
    public $id            = null;
    public $lev           = 1;
    public $lev_title     = 0;
    public $base_hp       = 100;
    public $base_dmg      = 100;
    public $lev_up_points = 0;
    public $award         = null;
    public $is_ok         = 1;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_lev_cfg';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'lev'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'lev_title'     => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'base_hp'       => ['db_type' => 'smallint', 'length' => 0, 'def' => 100, 'pro_def' => 100],
        'base_dmg'      => ['db_type' => 'smallint', 'length' => 0, 'def' => 100, 'pro_def' => 100],
        'lev_up_points' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'award'         => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'lev'       => intval($this->lev),
            'lev_title' => $this->lev_title,
            'base_dmg'  => intval($this->base_dmg),
            'base_hp'   => intval($this->base_hp),
            'award'     => $this->getJsondecodedValue($this->award, 'object'),
        ];
    }


}
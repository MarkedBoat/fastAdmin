<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int item_sn 关卡序号
 * @property string item_name 关卡名
 * @property string item_icon 关卡图标
 * @property string item_detail 描述
 * @property string stage_opt 关卡设置，游戏性的
 * @property string threshold 门槛达成后，才能进入,也是限制条件
 * @property string effect 影响,主要针对角色的
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class StageDao extends ORM
{
    public $id          = null;
    public $item_sn     = 0;
    public $item_name   = 0;
    public $item_icon   = 0;
    public $item_detail = 0;
    public $stage_opt   = null;
    public $threshold   = null;
    public $effect      = null;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_stage';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_sn'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_icon'   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'item_detail' => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'stage_opt'   => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'threshold'   => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'effect'      => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
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
            'id'          => intval($this->id),
            'item_name'   => $this->item_name,
            'item_detail' => $this->item_detail,
            'stage_opt'   => $this->getJsondecodedValue($this->stage_opt, 'object'),
        ];
    }


}
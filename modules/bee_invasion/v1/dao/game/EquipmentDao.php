<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string item_name 通货名称
 * @property string item_code 通货标识
 * @property string item_class 归类
 * @property string item_icon 通货图标
 * @property string item_detail 描述
 * @property int has_ui 是否正常  1:有  2:无
 * @property string threshold 使用门槛，达成后，才能使用,也是限制条件
 * @property string effect 装备后效果
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class EquipmentDao extends ORM
{
    public $id          = null;
    public $item_name   = 0;
    public $item_code   = 0;
    public $item_class  = null;
    public $item_icon   = 0;
    public $item_detail = 0;
    public $has_ui      = 1;
    public $threshold   = null;
    public $effect      = null;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_equipment';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_class'  => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_icon'   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'item_detail' => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'has_ui'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
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
            'item_code'   => $this->item_code,
            'item_icon'   => $this->item_icon,
            'item_detail' => $this->item_detail,
            'has_ui'      => intval($this->has_ui),
            'item_class'  => $this->getJsondecodedValue($this->threshold, 'array'),
            'threshold'   => $this->getJsondecodedValue($this->threshold, 'object'),
            'effect'      => $this->getJsondecodedValue($this->effect, 'object'),
        ];
    }


}
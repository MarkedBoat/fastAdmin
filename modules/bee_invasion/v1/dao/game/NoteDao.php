<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string item_class 票据归类
 * @property string item_name 票据名称
 * @property string item_code 票据标识
 * @property string item_icon 票据图标
 * @property string item_detail 票据描述
 * @property int has_ui 是否正常  1:有  2:无
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class NoteDao extends ORM
{
    public $id          = null;
    public $item_class  = 0;
    public $item_name   = 0;
    public $item_code   = 0;
    public $item_icon   = 0;
    public $item_detail = 0;
    public $has_ui      = 2;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_note';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_class'  => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'item_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_icon'   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'item_detail' => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'has_ui'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
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
        return $this->getOuterDataArray();
    }


}
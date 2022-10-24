<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\ORM;

/**
 * @property int id
 * @property string item_name 设置名
 * @property string item_code 对应的code
 * @property string item_detail 描述  设置
 * @property string setting 配置信息
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class ConfigDao extends ORM
{
    public $id          = null;
    public $item_name   = 0;
    public $item_code   = 0;
    public $item_detail = 0;
    public $setting     = null;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_config';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'item_detail' => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'setting'     => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
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
            'item_detail' => $this->item_detail,
            'setting'     => $this->getJsondecodedValue($this->setting, 'object'),

        ];
    }


}
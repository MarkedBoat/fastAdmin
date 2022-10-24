<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string item_name 分区名，频道名
 * @property string item_code
 * @property string item_icon 分区图标
 * @property string item_detail 分区描述
 * @property int has_ui 是否正常  1:有  2:无
 * @property string opts 设置项
 * @property string threshold 使用门槛，达成后，才能使用,也是限制条件
 * @property string effect 频道特权/加成
 * @property int order_num
 * @property int service_status 服务状态，1:on 2:off
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class ChannelDao extends ORM
{
    public $id             = null;
    public $item_name      = '';
    public $item_code      = '';
    public $item_icon      = '';
    public $item_detail    = '';
    public $has_ui         = 1;
    public $opts           = null;
    public $threshold      = null;
    public $effect         = null;
    public $order_num      = 0;
    public $service_status = 1;
    public $is_ok          = 1;
    public $create_time    = null;
    public $update_time    = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_channel';
    public static $pk           = 'id';
    public static $field_config = [
        'id'             => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_name'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_code'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_icon'      => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'item_detail'    => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'has_ui'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'opts'           => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'threshold'      => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'effect'         => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'order_num'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'service_status' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_ok'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'             => intval($this->id),
            'item_name'      => $this->item_name,
            'item_code'      => $this->item_code,
            'item_icon'      => $this->item_icon,
            'item_detail'    => $this->item_detail,
            'has_ui'         => intval($this->has_ui),
            'opts'           => $this->getJsondecodedValue($this->opts, 'object'),
            'threshold'      => $this->getJsondecodedValue($this->threshold, 'object'),
            'effect'         => $this->getJsondecodedValue($this->effect, 'object'),
            'service_status' => $this->service_status === 1
        ];
    }


}
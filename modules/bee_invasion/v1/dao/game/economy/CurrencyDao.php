<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string item_class 通货归类
 * @property string item_name 通货名称
 * @property string item_code 通货标识
 * @property string item_icon 通货图标
 * @property string item_detail 描述
 * @property int decimal_places 小数位数，因为要算个的东西
 * @property string opts 配置信息
 * @property int has_ui 是否正常  1:有  2:无
 * @property int cash_price 现金兑换价格，单位  1/100 元，如果是 单价是 1元/个 值: 100 ，1.8元 180，0代表没有现金价格
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class CurrencyDao extends ORM
{
    public $id             = null;
    public $item_class     = '';
    public $item_name      = '';
    public $item_code      = '';
    public $item_icon      = '';
    public $item_detail    = '';
    public $decimal_places = 0;
    public $opts           = null;
    public $has_ui         = 1;
    public $cash_price     = 0;
    public $is_ok          = 1;
    public $create_time    = null;
    public $update_time    = null;


    public static $_fields_str;
    public static $tableName    = 'bi_currency';
    public static $pk           = 'id';
    public static $field_config = [
        'id'             => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'item_class'     => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'item_name'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_code'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_icon'      => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'item_detail'    => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'decimal_places' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'opts'           => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'has_ui'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'cash_price'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
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
            'id'          => intval($this->id),
            'item_name'   => $this->item_name,
            'item_code'   => $this->item_code,
            'item_detail' => $this->item_detail,
            'has_ui'      => intval($this->has_ui),
            'cash_price'  => intval($this->cash_price),
        ];
    }


}
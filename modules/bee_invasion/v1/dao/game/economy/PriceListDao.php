<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string pay_item_class 支付物  类型  currency 通货
 * @property string pay_item_code 支付物标识
 * @property int pay_item_amount 支付物 数量
 * @property string payment_name 付方式的名称
 * @property string goods_item_class 商品 条目  cg消耗性道具  equipment 装备
 * @property string goods_item_code 商品 名
 * @property int goods_item_amount 商品 数量
 * @property string goods_name 商品名称
 * @property string item_detail 描述
 * @property string item_type 类型  custom:自定义数量 ,定义的是goods的数量    pkg:正常的套餐包
 * @property int has_ui 是否正常  1:有  2:无
 * @property string ui_info ui 展示用的数据
 * @property int order_num
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class PriceListDao extends ORM
{
    public $id                = null;
    public $pay_item_class    = 0;
    public $pay_item_code     = 0;
    public $pay_item_amount   = 1000;
    public $payment_name      = 0;
    public $goods_item_class  = null;
    public $goods_item_code   = 0;
    public $goods_item_amount = 0;
    public $goods_name        = 0;
    public $item_detail       = 0;
    public $item_type         = 0;
    public $has_ui            = 1;
    public $ui_info           = null;
    public $order_num         = 0;
    public $is_ok             = 1;
    public $create_time       = null;
    public $update_time       = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_shop_price_list';
    public static $pk           = 'id';
    public static $field_config = [
        'id'                => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'pay_item_class'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'pay_item_code'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'pay_item_amount'   => ['db_type' => 'int', 'length' => 0, 'def' => 1000, 'pro_def' => 1000],
        'payment_name'      => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'goods_item_class'  => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'goods_item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'goods_item_amount' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'goods_name'        => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'item_detail'       => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'item_type'         => ['db_type' => 'varchar', 'length' => 16, 'def' => 'pkg', 'pro_def' => 0],
        'has_ui'            => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'ui_info'           => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'order_num'         => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'             => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'       => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'       => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'                => intval($this->id),
            'pay_item_class'    => $this->pay_item_class,
            'pay_item_code'     => $this->pay_item_code,
            'pay_item_amount'   => $this->pay_item_amount,
            'payment_name'      => $this->payment_name,
            'cash_price_value'  => $this->pay_item_code === 'cash' ? ($this->pay_item_amount) : 0,
            'goods_item_class'  => $this->getJsondecodedValue($this->goods_item_class, 'array'),
            'ui_info'           => $this->getJsondecodedValue($this->ui_info, 'object'),
            'goods_item_code'   => $this->goods_item_code,
            'goods_item_amount' => $this->goods_item_amount,
            'goods_name'        => $this->goods_name,
            'item_type'         => $this->item_type,
            'order_num'         => $this->order_num,
            'item_detail'       => $this->item_detail,
            'has_ui'            => intval($this->has_ui)
        ];
    }


}
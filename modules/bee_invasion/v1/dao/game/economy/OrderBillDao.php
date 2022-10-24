<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int order_id order.id
 * @property int user_id 用户id
 * @property string payment_code 支付类型，cash  现金，其他去查currency.item_code
 * @property string price_detail 商品价格，因为套餐的存在，可能是没法整除的，值为一个比例：[pay,goods]
 * @property string goods_class 商品类型  cg消耗性道具  equipment 装备
 * @property string goods_code 货物code currency.item_code 或者  gc.currency.item_code
 * @property int goods_amount 商品的数量
 * @property int bill_sum 当前商品的总金额，采用最小单位，现金为分
 * @property string create_time
 * @property string update_time
 */
class OrderBillDao extends ORM
{
    public $id           = null;
    public $order_id     = 0;
    public $user_id      = 0;
    public $payment_code = 0;
    public $price_detail = null;
    public $goods_class  = null;
    public $goods_code   = 0;
    public $goods_amount = 0;
    public $bill_sum     = 0;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_order_bill';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'order_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'payment_code' => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'price_detail' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'goods_class'  => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'goods_code'   => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'goods_amount' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'bill_sum'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'create_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'           => intval($this->id),
            'order_id'     => $this->order_id,
            'user_id'      => $this->user_id,
            'payment_code' => $this->payment_code,
            'price_detail' => $this->getJsondecodedValue($this->price_detail, 'array'),
            'goods_class'  => $this->getJsondecodedValue($this->goods_class, 'array'),
            'goods_code'   => $this->goods_code,
            'goods_amount' => $this->goods_amount,
            'bill_sum'     => $this->bill_sum
        ];
    }


}
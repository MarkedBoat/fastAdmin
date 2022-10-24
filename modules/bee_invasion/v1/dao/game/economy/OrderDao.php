<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string open_id 对外展示的id
 * @property int order_type 订单类型  1:内部  2:外部
 * @property string payment_code 支付类型，cash  现金，其他去查currency.item_code
 * @property string payment_platform 支付平台, wechat/alipay
 * @property string title 订单名称
 * @property string detail 订单描述
 * @property int user_id 用户id
 * @property int order_sum 订单金额，采用最小单位，现金为分
 * @property string user_token 用户token
 * @property string ip 发起订单的ip
 * @property int is_show 是否展示，有些订单不希望展示  1:yes  2:no
 * @property int is_payed 是否支付  1:yes  2:no
 * @property int is_complete 是否交付完成了
 * @property int is_refund 是否退库  1:yes  2:no
 * @property int is_cls 是否关闭 1:yes  2:no
 * @property string payed_time 支付时间
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 */
class OrderDao extends ORM
{
    public $id               = null;
    public $open_id          = 0;
    public $order_type       = 1;
    public $payment_code     = 0;
    public $payment_platform = 0;
    public $title            = 0;
    public $detail           = 0;
    public $user_id          = 0;
    public $order_sum        = 0;
    public $user_token       = 0;
    public $ip               = 0;
    public $is_show          = 1;
    public $is_payed         = 2;
    public $is_complete      = 2;
    public $is_refund        = 2;
    public $is_cls           = 2;
    public $payed_time       = null;
    public $is_ok            = 1;
    public $create_time      = null;
    public $update_time      = null;


    public static $_fields_str;
    public static $tableName    = 'bi_order';
    public static $pk           = 'id';
    public static $field_config = [
        'id'               => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'open_id'          => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'order_type'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'payment_code'     => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'payment_platform' => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => 0],
        'title'            => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'detail'           => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'user_id'          => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'order_sum'        => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_token'       => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'ip'               => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => 0],
        'is_show'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_payed'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_complete'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_refund'        => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_cls'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'payed_time'       => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'            => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'               => intval($this->id),
            'order_type'       => $this->order_type,
            'payment_code'     => $this->payment_code,
            'payment_platform' => $this->payment_platform,
            'title'            => $this->title,
            'detail'           => $this->detail,
            'user_id'          => $this->user_id,
            'order_sum'        => $this->order_sum,
            'ip'               => $this->ip,
            'is_show'          => $this->is_show,
            'is_payed'         => $this->is_payed,
            'is_refund'        => $this->is_refund,
            'payed_time'       => $this->payed_time,
            'is_ok'            => $this->is_ok,
        ];
    }


}
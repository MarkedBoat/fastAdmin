<?php

namespace modules\bee_invasion\v1\dao\com;

use models\common\db\ORM;

/**
 * @property int id
 * @property string project_name 项目
 * @property string proj_table_name 表名
 * @property string proj_data_id 表行id
 * @property string user_id
 * @property string item_class 添加的物品的归类
 * @property string item_code 添加的具体物品
 * @property int item_amount 添加了多少
 * @property string order_detail 描述
 * @property string order_info 订单信息
 * @property string remark 备注
 * @property int is_err 是否有错
 * @property int is_add 是否添加了  1:是  2:否
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class AddOrderDao extends ORM
{
    public $id              = null;
    public $project_name    = '';
    public $proj_table_name = '';
    public $proj_data_id    = '';
    public $user_id         = '';
    public $item_class      = '';
    public $item_code       = '';
    public $item_amount     = 0;
    public $order_detail    = '';
    public $order_info      = null;
    public $remark          = '';
    public $is_err          = 2;
    public $is_add          = 2;
    public $is_ok           = 1;
    public $create_time     = null;
    public $update_time     = null;


    public static $_fields_str;
    public static $tableName    = 'bi_com_add_order';
    public static $pk           = 'id';
    public static $field_config = [
        'id'              => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'project_name'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'proj_table_name' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'proj_data_id'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'user_id'         => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'item_class'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_code'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_amount'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'order_detail'    => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'order_info'      => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'remark'          => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'is_err'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_add'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'     => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'     => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
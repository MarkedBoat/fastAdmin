<?php

namespace modules\bee_invasion\v1\dao\admin\bizuser;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string biz_user_id
 * @property string item_class 添加的物品的归类
 * @property string item_code 添加的具体物品
 * @property int item_amount 添加了多少,8位精度
 * @property string op_flag 操作唯一标识，不能重复
 * @property string op_detail 描述
 * @property int op_type 操作类型　１增　２减
 * @property string op_admin
 * @property int is_complete 是否 完成  1 y 2n
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class AssetsOpLog extends ORM
{
    public $id          = null;
    public $biz_user_id = '业务用户id';
    public $item_class  = '';
    public $item_code   = '';
    public $item_amount = 0;
    public $op_flag     = '';
    public $op_detail   = '';
    public $op_type     = 0;
    public $op_admin    = '后台操作员id';
    public $is_complete = 2;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_bg_bizuser_assets_op_log';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'biz_user_id' => ['db_type' => 'varchar', 'length' => 32, 'def' => '业务用户id', 'pro_def' => '业务用户id'],
        'item_class'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'item_amount' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'op_flag'     => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'op_detail'   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'op_type'     => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'op_admin'    => ['db_type' => 'varchar', 'length' => 32, 'def' => '后台操作员id', 'pro_def' => '后台操作员id'],
        'is_complete' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string src 来源
 * @property string src_id 操作来源唯一标识
 * @property int user_id 用户id
 * @property string item_code 实物 code
 * @property int item_amount 本次  实物 数量
 * @property int src_op_step 来源操作步骤，非必须，有些场景下来源操作比较发杂，记录下进行到哪有助于恢复数据
 * @property string src_open_id 操作来源  对外标识，可空
 * @property string src_remark 操作来源 备注
 * @property string user_name 用户姓名
 * @property string user_addr 用户地址
 * @property string user_tel 用户手机号
 * @property string express_info 物流信息
 * @property string create_time
 * @property string update_time
 */
class UserObjectHisDao extends ORM
{
    public $id           = null;
    public $src          = '';
    public $src_id       = '';
    public $user_id      = 0;
    public $item_code    = '0';
    public $item_amount  = 0;
    public $src_op_step  = 1;
    public $src_open_id  = '';
    public $src_remark   = '';
    public $user_name    = '';
    public $user_addr    = '';
    public $user_tel     = '';
    public $express_info = null;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_object_his';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'bigint', 'length' => 0, 'def' => null, 'pro_def' => null],
        'src'          => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'src_id'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'    => ['db_type' => 'varchar', 'length' => 32, 'def' => '0', 'pro_def' => '0'],
        'item_amount'  => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'src_op_step'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'src_open_id'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'src_remark'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'user_name'    => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'user_addr'    => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'user_tel'     => ['db_type' => 'varchar', 'length' => 11, 'def' => '', 'pro_def' => ''],
        'express_info' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'create_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
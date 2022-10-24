<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string src 来源，1:自动充值  2:一级收益  3:二级邀请者收益
 * @property string src_id 操作来源唯一标识
 * @property int user_id 用户id
 * @property int item_code 消耗性道具id
 * @property int item_amount 本次  消耗品 数量
 * @property int curr_amount 现有数量
 * @property int expect_amount 期望数量，可能回退
 * @property int src_op_type 来源操作类型   1:增  2:减
 * @property int src_op_step 来源操作步骤，非必须，有些场景下来源操作比较发杂，记录下进行到哪有助于恢复数据
 * @property string src_open_id 操作来源  对外标识，可空
 * @property string src_remark 操作来源 备注
 * @property string create_time
 * @property string update_time
 */
class UserCgHisDao extends ORM
{
    public $id            = null;
    public $src           = 0;
    public $src_id        = 0;
    public $user_id       = 0;
    public $item_code     = 0;
    public $item_amount   = 0;
    public $curr_amount   = 0;
    public $expect_amount = 0;
    public $src_op_type   = 1;
    public $src_op_step   = 1;
    public $src_open_id   = 0;
    public $src_remark    = 0;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_user_cg_his';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'bigint', 'length' => 0, 'def' => null, 'pro_def' => null],
        'src'           => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'src_id'        => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'user_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '0', 'pro_def' => 0],
        'item_amount'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'curr_amount'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'expect_amount' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'src_op_type'   => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'src_op_step'   => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'src_open_id'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'src_remark'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
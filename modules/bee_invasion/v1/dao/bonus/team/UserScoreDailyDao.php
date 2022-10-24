<?php

namespace modules\bee_invasion\v1\dao\bonus\team;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int db_part_id 数据分区id
 * @property int user_id 用户id
 * @property int ymd 8位日期
 * @property int daily_score_sum 分数，成绩
 * @property int before_score_sum 分数，成绩
 * @property int after_score_sum 分数，成绩
 * @property int before_lev 操作前级别
 * @property int after_lev 操作后级别
 * @property int take_num 分红多少
 * @property int op_step 1: addup on user team   2: added user currency
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class UserScoreDailyDao extends ORM
{
    public $id               = null;
    public $db_part_id       = 0;
    public $user_id          = 0;
    public $ymd              = 0;
    public $daily_score_sum  = 0;
    public $before_score_sum = 0;
    public $after_score_sum  = 0;
    public $before_lev       = 0;
    public $after_lev        = 0;
    public $take_num         = 0;
    public $op_step          = 0;
    public $is_ok            = 1;
    public $create_time      = null;
    public $update_time      = null;


    public static $_fields_str;
    public static $tableName    = 'bi_bonus_user_score_daily';
    public static $pk           = 'id';
    public static $field_config = [
        'id'               => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'db_part_id'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_id'          => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'ymd'              => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'daily_score_sum'  => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'before_score_sum' => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'after_score_sum'  => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'before_lev'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'after_lev'        => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'take_num'         => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'op_step'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'            => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
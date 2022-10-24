<?php

namespace modules\bee_invasion\v1\dao\bonus\team;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id 即user_id
 * @property int db_part_id 数据分区id
 * @property int user_id 用户id
 * @property int score_sum 分数，成绩
 * @property int lev 级别
 * @property string pid_path 父级路径，从小到大
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class UserTeamDao extends ORM
{
    public $id          = null;
    public $db_part_id  = 0;
    public $user_id     = 0;
    public $score_sum   = 0;
    public $lev         = 0;
    public $pid_path    = null;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_bonus_user_team';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'db_part_id'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_id'     => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'score_sum'   => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'lev'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'pid_path'    => ['db_type' => 'longtext', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
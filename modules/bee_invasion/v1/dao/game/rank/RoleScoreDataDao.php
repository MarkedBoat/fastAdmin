<?php

namespace modules\bee_invasion\v1\dao\game\rank;

use models\common\db\ORM;
use models\common\error\AdvError;

/**
 * @property int id
 * @property string channel_code 频道
 * @property int date_index 时间index,比如每天的 yyyymmdd
 * @property int user_id 用户id
 * @property int stages_score 用户分数
 * @property string scores
 * @property int is_fake 是否假用户  1:是  2:否
 * @property int is_ok 是否正常  1:是  2:否
 * @property string create_time
 * @property string update_time
 */
class RoleScoreDataDao extends ORM
{
    public $id           = null;
    public $channel_code = 'public_channel';
    public $date_index   = 0;
    public $user_id      = 0;
    public $stages_score = 0;
    public $scores       = null;
    public $is_fake      = 2;
    public $is_ok        = 1;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_user_rank_data';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'channel_code' => ['db_type' => 'varchar', 'length' => 32, 'def' => 'public_channel', 'pro_def' => 'public_channel'],
        'date_index'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'stages_score' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'scores'       => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_fake'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'        => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
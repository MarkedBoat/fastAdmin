<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string channel_code 频道
 * @property int date_index 时间index,比如每天的 yyyymmdd
 * @property int rank_sn 序号
 * @property int user_id 用户id
 * @property int award_sn 奖励sn
 * @property string item_class 奖励类型
 * @property string item_code 奖励code
 * @property int item_amount 物品数量
 * @property int award_status 奖励专题
 * @property string award_detail 奖品描述
 * @property int his_id 对应item_class历史的id
 * @property int is_fake 是否假用户  1:是  2:否
 * @property int is_ok 是否正常  1:是  2:否
 * @property string create_time
 * @property string update_time
 */
class UserRankAwardHisDao extends ORM
{
    public $id           = null;
    public $channel_code = 'public_channel';
    public $date_index   = 0;
    public $rank_sn      = 1;
    public $user_id      = 0;
    public $award_sn     = 0;
    public $item_class   = '';
    public $item_code    = '';
    public $item_amount  = 1;
    public $award_status = 1;
    public $award_detail = '';
    public $his_id       = 0;
    public $is_fake      = 2;
    public $is_ok        = 1;
    public $create_time  = null;
    public $update_time  = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_user_rank_award_his';
    public static $pk           = 'id';
    public static $field_config = [
        'id'           => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'channel_code' => ['db_type' => 'varchar', 'length' => 32, 'def' => 'public_channel', 'pro_def' => 'public_channel'],
        'date_index'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'rank_sn'      => ['db_type' => 'smallint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'user_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'award_sn'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_class'   => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'item_code'    => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'item_amount'  => ['db_type' => 'smallint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'award_status' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'award_detail' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'his_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
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
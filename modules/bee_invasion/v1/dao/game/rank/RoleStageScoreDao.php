<?php

namespace modules\bee_invasion\v1\dao\game\rank;

use models\common\db\ORM;
use models\common\error\AdvError;

/**
 * @property int id
 * @property int user_id 用户id
 * @property int ymd yyyymmdd
 * @property string channel_code 分区
 * @property string channel_note
 * @property int stage_index 关卡
 * @property int score
 * @property int report_id
 * @property int has_exception 是否异常
 * @property string errors
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class RoleStageScoreDao extends ORM
{
    public $id            = null;
    public $user_id       = 0;
    public $ymd           = 0;
    public $channel_code  = 'public_channel';
    public $channel_note  = '';
    public $stage_index   = 0;
    public $score         = 0;
    public $report_id     = 0;
    public $has_exception = 2;
    public $errors        = '';
    public $is_ok         = 1;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_stage_score';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'ymd'           => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'channel_code'  => ['db_type' => 'varchar', 'length' => 32, 'def' => 'public_channel', 'pro_def' => 'public_channel'],
        'channel_note'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'stage_index'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'score'         => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'report_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'has_exception' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'errors'        => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'is_ok'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    /**
     * 获取分数总和
     * @param string $channel_code 分区
     * @param int $ymd 8位日期
     * @param int $user_id
     * @return int
     * @throws AdvError
     */
    public function getUserScoreSum($channel_code, $ymd, $user_id = 0)
    {
        if (empty($user_id))
        {
            if (empty($this->user_id))
            {
                throw new AdvError(AdvError::code_error, '两个user_id 不能全为空');
            }
            $user_id = $this->user_id;
        }

        $tn   = $this->getTableName();
        $sql  = "select sum(score) from {$tn} where ymd=:ymd and user_id=:user_id";
        $sql  = "SELECT max(score) as score,stage_index FROM {$tn} where channel_code=:ch_code and  ymd=:ymd and user_id=:user_id group  by stage_index;";
        $rows = $this->getDbConnect()->setText($sql)->bindArray([':ch_code' => $channel_code, ':ymd' => $ymd, ':user_id' => $user_id])->queryAll();
        return array_sum(array_column($rows, 'score'));
        // return intval($this->getDbConnect()->setText($sql)->bindArray([':ymd' => $ymd, ':user_id' => $user_id])->queryScalar());
    }


}
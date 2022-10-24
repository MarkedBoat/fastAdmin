<?php

namespace modules\bee_invasion\v1\dao\game\stage;

use models\common\db\ORM;

/**
 * @property int id role id
 * @property int user_id user_id
 * @property string report_data 上报数据
 * @property int has_exception 是否异常
 * @property string errors
 * @property int score_id 分数统计表bi_game_role_stage_score的id
 * @property string create_time
 * @property string update_time
 */
class StageReportDao extends ORM
{
    public $id            = null;
    public $user_id       = 0;
    public $report_data   = null;
    public $has_exception = 2;
    public $errors        = '';
    public $score_id      = 0;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_report';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'report_data'   => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'has_exception' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'errors'        => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'score_id'      => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return [
            'id'          => intval($this->id),
            'report_data' => $this->getJsondecodedValue($this->report_data, 'object'),
        ];
    }


}
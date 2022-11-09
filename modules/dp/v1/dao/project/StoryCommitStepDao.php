<?php

namespace modules\dp\v1\dao\project;

use models\common\db\ORM;

/**
 * @property int id
 * @property string title 步骤标题
 * @property string item_code 步骤code
 * @property string detail 步骤描述
 * @property int step_from 步骤来源  1:机器生成  2:人工选择
 * @property int desc_order 倒叙序号
 * @property int is_ok
 * @property string create_time
 */
class StoryCommitStepDao extends ORM
{
    public $id          = null;
    public $title       = 'post';
    public $item_code   = 'post';
    public $detail      = null;
    public $step_from   = 0;
    public $desc_order  = 0;
    public $is_ok       = 1;
    public $create_time = null;


    public static $_fields_str;
    public static $tableName    = 'd_story_commit_step';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'       => ['db_type' => 'varchar', 'length' => 32, 'def' => 'post', 'pro_def' => 'post'],
        'item_code'   => ['db_type' => 'varchar', 'length' => 32, 'def' => 'post', 'pro_def' => 'post'],
        'detail'      => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'step_from'   => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'desc_order'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
<?php

namespace modules\dp\v1\dao\project;

use models\common\db\ORM;

/**
 * @property int id
 * @property int project_id 项目id
 * @property int story_id story id
 * @property int enter_by 谁移入的
 * @property int exit_by 谁移出的
 * @property string enter_time 移入时间
 * @property string exit_time 移出时间
 * @property int is_ok 是否正常  1:是  2:否
 */
class ProjectStoryDao extends ORM
{
    public $id         = null;
    public $project_id = 0;
    public $story_id   = 0;
    public $enter_by   = 0;
    public $exit_by    = 0;
    public $enter_time = null;
    public $exit_time  = null;
    public $is_ok      = 1;


    public static $_fields_str;
    public static $tableName    = 'd_project_story';
    public static $pk           = 'id';
    public static $field_config = [
        'id'         => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'project_id' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'story_id'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'enter_by'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'exit_by'    => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'enter_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'exit_time'  => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
    ];

    public function getDbConfName()
    {
        return 'dp';
    }

}
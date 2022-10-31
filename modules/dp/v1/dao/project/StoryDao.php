<?php

namespace modules\dp\v1\dao\project;

use models\common\db\ORM;

/**
 * @property int id
 * @property int project_id 项目id
 * @property int version_id 版本id
 * @property int story_id 父级id
 * @property int story_desc_order 级别 越大越高
 * @property string title 标题
 * @property string detail 描述
 * @property string start_date 预期开始时间
 * @property string end_date 预期结束时间
 * @property int create_by admin_id
 * @property int is_close 是否关闭
 * @property int is_ok 是否正常  1:是  2:否
 * @property string create_time
 * @property string update_time
 */
class StoryDao extends ORM
{
    public $id               = null;
    public $project_id       = 1;
    public $version_id       = 1;
    public $story_id         = 1;
    public $story_desc_order = 1;
    public $title            = '';
    public $detail           = null;
    public $start_date       = null;
    public $end_date         = null;
    public $create_by        = 0;
    public $is_close         = 2;
    public $is_ok            = 1;
    public $create_time      = null;
    public $update_time      = null;


    public static $_fields_str;
    public static $tableName    = 'd_story';
    public static $pk           = 'id';
    public static $field_config = [
        'id'               => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'project_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'version_id'       => ['db_type' => 'int', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'story_id'         => ['db_type' => 'int', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'story_desc_order' => ['db_type' => 'smallint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'title'            => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'detail'           => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'start_date'       => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'end_date'         => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'create_by'        => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_close'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'            => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
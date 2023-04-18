<?php

namespace modules\dp\v1\dao\project;

use models\common\db\ORM;

/**
 * @property int id
 * @property int version_id 目标版本id
 * @property int story_id story id
 * @property int is_ok 是否正常  1:是  2:否
 */
class StoryVersionDao extends ORM
{
    public $id         = null;
    public $version_id = 0;
    public $story_id   = 0;
    public $is_ok      = 1;


    public static $_fields_str;
    public static $tableName    = 'd_story_version';
    public static $pk           = 'id';
    public static $field_config = [
        'id'         => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'version_id' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'story_id'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
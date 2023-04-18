<?php

namespace modules\dp\v1\dao\project;

use models\common\db\ORM;

/**
 * @property int id
 * @property int story_id story id
 * @property int reply_id 回复哪个d_story_step.id
 * @property string detail 描述
 * @property int used_hours 花费时间
 * @property int create_by admin_id
 * @property string step post 提交创建,confirm 需求等确认,dev 开发中,test 测试,pre 预览测试 ,prod 线上测试,feedback 运营反馈 ,end  结束 ,close 非正常关闭,del  删除
 * @property string create_time
 */
class StoryCommitDao extends ORM
{
    public $id          = null;
    public $story_id    = 0;
    public $reply_id    = 0;
    public $detail      = null;
    public $used_hours  = 1;
    public $create_by   = 0;
    public $step        = 'post';
    public $create_time = null;


    public static $_fields_str;
    public static $tableName    = 'd_story_commit';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'story_id'    => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'reply_id'    => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'detail'      => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'used_hours'  => ['db_type' => 'int', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_by'   => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'step'        => ['db_type' => 'varchar', 'length' => 32, 'def' => 'post', 'pro_def' => 'post'],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
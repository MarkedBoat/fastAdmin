<?php

namespace modules\dp\v1\dao\task;

use models\common\db\ORM;

/**
 * @property int id
 * @property string op 操作类型
 * @property string op_flag 操作唯一标识
 * @property int op_mod 以50 mod id ，用于分配任务
 * @property string op_param 参数
 * @property int is_complete 是否完成  1:是  2:否
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class AsyncTaskDao extends ORM
{
    public $id          = null;
    public $op          = '';
    public $op_flag     = '';
    public $op_mod      = 50;
    public $op_param    = null;
    public $is_complete = 1;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'game_async_task';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'op'          => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'op_flag'     => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'op_mod'      => ['db_type' => 'int', 'length' => 0, 'def' => 50, 'pro_def' => 50],
        'op_param'    => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_complete' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
<?php

namespace modules\dp\v1\dao\admin\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string db_name
 * @property string table_name
 * @property int row_pk 对应的pk
 * @property int exec_res 执行结果
 * @property string op_type
 * @property string exec_info 执行信息
 * @property int exec_by admin_id
 * @property string log_struct_ver
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 */
class DbOpLogDao extends ORM
{
    public $id             = null;
    public $db_name        = '';
    public $table_name     = '';
    public $row_pk         = 0;
    public $exec_res       = 0;
    public $op_type        = 'update/insert/insert_update';
    public $exec_info      = null;
    public $exec_by       = 0;
    public $log_struct_ver = '日子结构版本';
    public $is_ok          = 1;
    public $create_time    = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_op_log';
    public static $pk           = 'id';
    public static $field_config = [
        'id'             => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'db_name'        => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'table_name'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'row_pk'         => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'exec_res'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'op_type'        => ['db_type' => 'varchar', 'length' => 64, 'def' => 'update/insert/insert_update', 'pro_def' => 'update/insert/insert_update'],
        'exec_info'      => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'exec_by'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'log_struct_ver' => ['db_type' => 'varchar', 'length' => 64, 'def' => '日子结构版本', 'pro_def' => '日子结构版本'],
        'is_ok'          => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
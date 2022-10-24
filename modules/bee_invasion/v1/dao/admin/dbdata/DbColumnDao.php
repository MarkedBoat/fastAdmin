<?php

namespace modules\bee_invasion\v1\dao\admin\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string title
 * @property string db_name
 * @property string table_name
 * @property string column_name
 * @property int column_sn 字段序号，主要用于页面排版
 * @property string val_range 值范围类型
 * @property string db_datatype 数据库记录的数据类型
 * @property int db_datatype_len 字段长度
 * @property string out_datatype 作为什么类型输出
 * @property string in_datatype 写入的接受什么类型
 * @property string index_key
 * @property string default_val
 * @property string remark 备注
 * @property string read_roles 读roles
 * @property string opt_roles 选择写roles ，只能在已有value中选择填写
 * @property string add_roles 可添加value 的roles
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbColumnDao extends ORM
{
    public static $_fields_str;
    public static $tableName    = 'bi_bg_db_column';
    public static $pk           = 'id';
    public static $field_config = [
        'id'              => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'           => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'db_name'         => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'table_name'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'column_name'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'column_sn'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 100, 'pro_def' => 100],
        'val_range'       => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'db_datatype'     => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'db_datatype_len' => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'out_datatype'    => ['db_type' => 'varchar', 'length' => 16, 'def' => 'string', 'pro_def' => 'string'],
        'in_datatype'     => ['db_type' => 'varchar', 'length' => 16, 'def' => 'string', 'pro_def' => 'string'],
        'index_key'       => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'default_val'     => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'remark'          => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'read_roles'      => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'opt_roles'       => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'add_roles'       => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'     => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'     => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
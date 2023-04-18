<?php

namespace modules\_dp\v1\dao\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string index_dbconf_name 主表 db
 * @property string index_table_name 主表 表名
 * @property string index_column_name 主表 列名
 * @property string ext_filter_sql 主表 列值，可能用 src 和 src_id 的组合代表的意思，甚至  a=x and b=x and c=x 才能确定src_id 是对应的哪个表的值
 * @property string explain_dbconf_name
 * @property string explain_table_name
 * @property string explain_column_name 用于解释表的对应的字段
 * @property string explain_label_column_name 从用于表中 取出一个字段作为label, src_safe_columns取的信息会作为 info 附加上去(于label同级)
 * @property string explain_ext_columns 用于解释表中，有些关联字段，是敏感的，得去掉
 * @property int as_filter 是否作为 筛选项
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbColumnExplainDao extends ORM
{

    public $id                        = null;
    public $index_dbconf_name         = '';
    public $index_table_name          = '';
    public $index_column_name         = '';
    public $ext_filter_sql            = '';
    public $explain_dbconf_name       = '';
    public $explain_table_name        = '';
    public $explain_column_name       = '';
    public $explain_label_column_name = '';
    public $explain_ext_columns       = '';
    public $as_filter                 = 2;
    public $is_ok                     = 1;
    public $create_time               = null;
    public $update_time               = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_column_explain';
    public static $pk           = 'id';
    public static $field_config = [
        'id'                        => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'index_dbconf_name'         => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'index_table_name'          => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'index_column_name'         => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'ext_filter_sql'            => ['db_type' => 'varchar', 'length' => 120, 'def' => '', 'pro_def' => ''],
        'explain_dbconf_name'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'explain_table_name'        => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'explain_column_name'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'explain_label_column_name' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'explain_ext_columns'       => ['db_type' => 'varchar', 'length' => 512, 'def' => '', 'pro_def' => ''],
        'as_filter'                 => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'                     => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'               => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'               => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return '_sys_';
    }

}
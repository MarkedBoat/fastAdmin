<?php

namespace modules\dp\v1\dao\admin\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string db_name 主表 db
 * @property string table_name 主表 表名
 * @property string column_name 主表 列名
 * @property string ext_filter_sql 主表 列值，可能用 src 和 src_id 的组合代表的意思，甚至  a=x and b=x and c=x 才能确定src_id 是对应的哪个表的值
 * @property string src_db_name
 * @property string src_table_name
 * @property string src_val_column_name 被关联表的对应的字段
 * @property string src_label_column_name 从被关联表中 取出一个字段作为label, src_safe_columns取的信息会作为 info 附加上去(于label同级)
 * @property string src_safe_columns 有些关联字段，是敏感的，得去掉
 * @property string relation_type 关联关系， belong,has many,many to many
 * @property int as_filter 是否作为 筛选项
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbRelationDao extends ORM
{
    public $id                    = null;
    public $db_name               = '';
    public $table_name            = '';
    public $column_name           = '';
    public $ext_filter_sql        = '';
    public $src_db_name           = '';
    public $src_table_name        = '';
    public $src_val_column_name   = '';
    public $src_label_column_name = '';
    public $src_safe_columns      = '';
    public $relation_type         = 'many_many';
    public $as_filter             = 2;
    public $is_ok                 = 1;
    public $create_time           = null;
    public $update_time           = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_column_relation';
    public static $pk           = 'id';
    public static $field_config = [
        'id'                    => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'db_name'               => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'table_name'            => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'column_name'           => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'ext_filter_sql'        => ['db_type' => 'varchar', 'length' => 120, 'def' => '', 'pro_def' => ''],
        'src_db_name'           => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'src_table_name'        => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'src_val_column_name'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'src_label_column_name' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'src_safe_columns'      => ['db_type' => 'varchar', 'length' => 512, 'def' => '', 'pro_def' => ''],
        'relation_type'         => ['db_type' => 'varchar', 'length' => 32, 'def' => 'many_many', 'pro_def' => 'many_many'],
        'as_filter'             => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'                 => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'           => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'           => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'dp';
    }

}
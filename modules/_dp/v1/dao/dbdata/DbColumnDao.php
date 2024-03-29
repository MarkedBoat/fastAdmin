<?php

namespace modules\_dp\v1\dao\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string title
 * @property string dbconf_name
 * @property string table_name
 * @property string column_name
 * @property int column_sn 字段序号，主要用于页面排版
 * @property string val_items 值范围类型
 * @property string val_items_link 值范围配置 $.link  请求ur 直接返回数组   ,$.jsonp 需要支持jsonp
 * @property string val_struct_code
 * @property string db_datatype 数据库记录的数据类型
 * @property int db_datatype_len 字段长度
 * @property string out_datatype 作为什么类型输出
 * @property string in_datatype 写入的接受什么类型
 * @property string index_key
 * @property string default_val
 * @property string remark 备注
 * @property string access_select_role_codes 可读 role codes
 * @property string access_update_role_codes 可改 role codes
 * @property string default_opts 默认选项
 * @property string query_input_type 查询输入类型  text/select/checkbox_list
 * @property int has_query_datalist 是否有datalist 进行辅助
 * @property int is_query_multi 查询是否能选择多个值，即in (a,b,c)
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbColumnDao extends ORM
{
    public $id                       = null;
    public $title                    = '';
    public $dbconf_name              = '';
    public $table_name               = '';
    public $column_name              = '';
    public $column_sn                = 100;
    public $val_items                = null;
    public $val_items_link           = null;
    public $val_struct_code          = '';
    public $db_datatype              = '';
    public $db_datatype_len          = 0;
    public $out_datatype             = 'string';
    public $in_datatype              = 'string';
    public $index_key                = '';
    public $default_val              = '';
    public $remark                   = '';
    public $access_select_role_codes = null;
    public $access_update_role_codes = null;
    public $default_opts             = null;
    public $query_input_type         = 'text';
    public $has_query_datalist       = 2;
    public $is_query_multi           = 2;
    public $is_ok                    = 1;
    public $create_time              = null;
    public $update_time              = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_column';
    public static $pk           = 'id';
    public static $field_config = [
        'id'                       => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'                    => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'dbconf_name'              => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'table_name'               => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'column_name'              => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'column_sn'                => ['db_type' => 'int', 'length' => 0, 'def' => 100, 'pro_def' => 100],
        'val_items'                => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'val_items_link'           => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'val_struct_code'          => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'db_datatype'              => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'db_datatype_len'          => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'out_datatype'             => ['db_type' => 'varchar', 'length' => 16, 'def' => 'string', 'pro_def' => 'string'],
        'in_datatype'              => ['db_type' => 'varchar', 'length' => 16, 'def' => 'string', 'pro_def' => 'string'],
        'index_key'                => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => ''],
        'default_val'              => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'remark'                   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'access_select_role_codes' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'access_update_role_codes' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'default_opts'             => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'query_input_type'         => ['db_type' => 'varchar', 'length' => 32, 'def' => 'text', 'pro_def' => 'text'],
        'has_query_datalist'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_query_multi'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'                    => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'              => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'              => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return '_sys_';
    }

}
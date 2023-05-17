<?php

namespace modules\_dp\v1\dao\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string title
 * @property string remark
 * @property string dbconf_name
 * @property string table_name
 * @property string pk_key
 * @property string orm_class 可以用来查询的类
 * @property string access_role_codes 授权  role codes
 * @property string access_insert_role_codes 可以insert role codes
 * @property string default_opts 默认选项
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbTableDao extends ORM
{
    public $id                       = null;
    public $title                    = '';
    public $remark                   = '';
    public $dbconf_name              = '';
    public $table_name               = '';
    public $pk_key                   = '';
    public $orm_class                = '';
    public $access_role_codes        = null;
    public $access_insert_role_codes = null;
    public $default_opts             = null;
    public $is_ok                    = 1;
    public $create_time              = null;
    public $update_time              = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_table';
    public static $pk           = 'id';
    public static $field_config = [
        'id'                       => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'                    => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'remark'                   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'dbconf_name'              => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'table_name'               => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'pk_key'                   => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'orm_class'                => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'access_role_codes'        => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'access_insert_role_codes' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'default_opts'             => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'                    => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'              => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'              => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return '_sys_';
    }

}
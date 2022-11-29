<?php

namespace modules\_dp\v1\dao\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string title 数据库配置名
 * @property string db_code 配置code
 * @property string conf_host host
 * @property string conf_port port
 * @property string conf_dbname dbname
 * @property string conf_username username
 * @property string conf_password password
 * @property string conf_charset charset
 * @property string read_roles 读授权 roles
 * @property string all_roles 写授权的roles
 * @property string remark 备注
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbDbConfDao extends ORM
{
    public $id            = null;
    public $title         = '';
    public $db_code       = '';
    public $conf_host     = '';
    public $conf_port     = '';
    public $conf_dbname   = '';
    public $conf_username = '';
    public $conf_password = '';
    public $conf_charset  = '';
    public $read_roles    = null;
    public $all_roles     = null;
    public $remark        = '';
    public $is_ok         = 1;
    public $create_time   = null;
    public $update_time   = null;


    public static $_fields_str;
    public static $tableName    = 'bg_db_dbconf';
    public static $pk           = 'id';
    public static $field_config = [
        'id'            => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'         => ['db_type' => 'varchar', 'length' => 128, 'def' => '', 'pro_def' => ''],
        'db_code'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_host'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_port'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_dbname'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_username' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_password' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'conf_charset'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'read_roles'    => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'all_roles'     => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'remark'        => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'is_ok'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'   => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return '_sys_';
    }

}
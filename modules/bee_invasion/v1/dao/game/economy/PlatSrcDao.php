<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string src_code 来源，fc:阜藏商城
 * @property string src_name 来源名称
 * @property string pri_key 我方密钥
 * @property string pub_key 我方密钥
 * @property string src_pub_key 我方密钥
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class PlatSrcDao extends ORM
{
    public $id          = null;
    public $src_code    = 0;
    public $src_name    = 0;
    public $pri_key     = null;
    public $pub_key     = null;
    public $src_pub_key = null;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_plat_src';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'src_code'    => ['db_type' => 'varchar', 'length' => 32, 'def' => 'fc', 'pro_def' => 0],
        'src_name'    => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'pri_key'     => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'pub_key'     => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'src_pub_key' => ['db_type' => 'text', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
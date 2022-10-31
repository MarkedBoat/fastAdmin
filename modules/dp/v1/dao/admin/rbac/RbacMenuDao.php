<?php

namespace modules\dp\v1\dao\admin\rbac;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int pid
 * @property string title
 * @property string url
 * @property string opts 配置信息
 * @property string remark 备注
 * @property int is_ok
 * @property int is_backend 是否是后台链接  1:后台  2:前台  3:外网链接
 * @property string create_time
 * @property string update_time
 */
class RbacMenuDao extends ORM
{
    public $id          = null;
    public $pid         = 0;
    public $title       = '';
    public $url         = '';
    public $opts        = null;
    public $remark      = '';
    public $is_ok       = 1;
    public $is_backend  = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bg_rbac_menu';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'pid'         => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'title'       => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'url'         => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'opts'        => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'remark'      => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_backend'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'dp';
    }

}
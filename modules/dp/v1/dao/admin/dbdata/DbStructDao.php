<?php

namespace modules\bee_invasion\v1\dao\admin\dbdata;

use models\common\db\ORM;

/**
 * @property int id
 * @property string title
 * @property string struct_code
 * @property string struct_json 结构json
 * @property string remark 备注
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbStructDao extends ORM
{
    public $id          = null;
    public $title       = '标题';
    public $struct_code = '结构code';
    public $struct_json = null;
    public $remark      = '';
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'dp_bg_db_struct';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'title'       => ['db_type' => 'varchar', 'length' => 128, 'def' => '标题', 'pro_def' => '标题'],
        'struct_code' => ['db_type' => 'varchar', 'length' => 128, 'def' => '结构code', 'pro_def' => '结构code'],
        'struct_json' => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'remark'      => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

}
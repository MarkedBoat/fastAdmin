<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id 用户id
 * @property string ad_note 广告凭证
 * @property string ad_cycle 广告凭证
 * @property int ad_sn 时间段内sn
 * @property int is_ok 是否正常  1:是  2:否
 * @property int is_handled 是否正常  1:是  2:否
 * @property string create_time
 * @property string update_time
 */
class UserAdHisDao extends ORM
{
    public $id          = null;
    public $user_id     = 0;
    public $ad_note     = '';
    public $ad_cycle    = '';
    public $ad_sn       = 0;
    public $is_ok       = 1;
    public $is_handled  = 2;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user_ad_his';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'ad_note'     => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'ad_cycle'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'ad_sn'       => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'is_handled'  => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
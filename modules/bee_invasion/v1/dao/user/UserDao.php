<?php

namespace modules\bee_invasion\v1\dao\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string open_id 对外开放id
 * @property string nickname 用户昵称
 * @property string avatar 头像
 * @property string mobile 手号，大陆地区
 * @property string password 用户密码
 * @property int sex 性别 1男 2女
 * @property string email 用户邮箱
 * @property string cdkey 用户注册邀请码
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string reg_time
 * @property string create_time
 * @property string update_time
 */
class UserDao extends ORM
{
    public $id          = null;
    public $open_id     = null;
    public $nickname    = '';
    public $avatar      = '';
    public $mobile      = '0';
    public $password    = '';
    public $sex         = 1;
    public $email       = '';
    public $cdkey       = '';
    public $is_ok       = 1;
    public $reg_time    = null;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_user';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'open_id'     => ['db_type' => 'varchar', 'length' => 32, 'def' => null, 'pro_def' => null],
        'nickname'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => ''],
        'avatar'      => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => ''],
        'mobile'      => ['db_type' => 'varchar', 'length' => 11, 'def' => '0', 'pro_def' => '0'],
        'password'    => ['db_type' => 'char', 'length' => 32, 'def' => '', 'pro_def' => ''],
        'sex'         => ['db_type' => 'tinyint', 'length' => 1, 'def' => 1, 'pro_def' => 1],
        'email'       => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => ''],
        //'cdkey'       => ['db_type' => 'char', 'length' => 6, 'def' => '', 'pro_def' => ''],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'reg_time'    => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getBasicInfo()
    {
        return [
            'id'          => intval($this->id),
            'open_id'     => $this->open_id,
            'nickname'    => $this->nickname,
            'avatar'      => $this->avatar,
            'mobile'      => $this->mobile,
            'reg_time'    => $this->reg_time,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];
    }


}
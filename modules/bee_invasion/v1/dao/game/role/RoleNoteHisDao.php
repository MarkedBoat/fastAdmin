<?php

namespace modules\bee_invasion\v1\dao\game\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string src 来源，1:自动充值  2:一级收益  3:二级邀请者收益
 * @property string src_id 操作来源唯一标识
 * @property int user_id 用户id
 * @property string item_code 票据code
 * @property string old_value 旧值
 * @property string new_value 新值
 * @property int src_op_type 来源操作类型   1:增  2:减
 * @property int src_op_step 来源操作步骤，非必须，有些场景下来源操作比较发杂，记录下进行到哪有助于恢复数据
 * @property string src_open_id 操作来源  对外标识，可空
 * @property string src_remark 操作来源 备注
 * @property string create_time
 * @property string update_time
 */
class RoleNoteHisDao extends ORM
{
    public $id          = null;
    public $src         = 0;
    public $src_id      = 0;
    public $user_id     = 0;
    public $item_code   = 0;
    public $old_value   = 0;
    public $new_value   = 0;
    public $src_op_type = 1;
    public $src_op_step = 1;
    public $src_open_id = 0;
    public $src_remark  = 0;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_role_note_his';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'bigint', 'length' => 0, 'def' => null, 'pro_def' => null],
        'src'         => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'src_id'      => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '0', 'pro_def' => 0],
        'old_value'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '0', 'pro_def' => 0],
        'new_value'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '0', 'pro_def' => 0],
        'src_op_type' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'src_op_step' => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'src_open_id' => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'src_remark'  => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];

    public function getDbConfName()
    {
        return 'bee_invade';
    }


}
<?php

namespace modules\bee_invasion\v1\dao\game;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property int user_id 票据所属人
 * @property string item_code 票据标识
 * @property string note_code 票据code
 * @property int expires 到期时间
 * @property int is_used 是否已经使用
 * @property int is_deny 是否被禁用
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class NoteListDao extends ORM
{
    public $id          = null;
    public $user_id     = 0;
    public $item_code   = 0;
    public $note_code   = 0;
    public $expires     = 0;
    public $is_used     = 2;
    public $is_deny     = 2;
    public $is_ok       = 1;
    public $create_time = null;
    public $update_time = null;


    public static $_fields_str;
    public static $tableName    = 'bi_game_note_list';
    public static $pk           = 'id';
    public static $field_config = [
        'id'          => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'user_id'     => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'note_code'   => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'expires'     => ['db_type' => 'bigint', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'is_used'     => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_deny'     => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_ok'       => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time' => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    public function getDbConfName()
    {
        return 'bee_invade';
    }

    public function getOpenInfo()
    {
        return $this->getOuterDataArray();
    }


}
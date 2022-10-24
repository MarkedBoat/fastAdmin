<?php

namespace modules\dev\v1\api;

use models\common\ActionBase;
use models\common\sys\Sys;
use models\ext\mail\TencentMailSmtp;
use modules\bee_invasion\v1\model\user\User;


class ActionTest extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }


    public function run()
    {
        $user_id                                   = $this->inputDataBox->tryGetInt('id');
        $db_block                                  = $this->inputDataBox->tryGetInt('prefix');
        Sys::app()->params['database_block_index'] = $db_block;

        $user_id   = $user_id ? $user_id : 100218;
        $open_code = User::trueId2OpenCode($user_id);
        $true_id   = User::openCode2TrueId($open_code);
        $db_block  = User::openCode2DbBlockIndex($open_code);
        return [
            'open_code' => $open_code,
            'id'        => $true_id,
            'prefix'    => $db_block,//其实是数据分区
        ];

        return base_convert('00', 36, 10);

        return base_convert(1295, 10, 36);

        return base_convert(11, 10, 36);

    }

}
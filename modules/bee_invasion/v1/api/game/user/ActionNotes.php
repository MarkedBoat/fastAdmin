<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Note;
use modules\bee_invasion\v1\model\role\RoleNote;


class ActionNotes extends GameBaseAction
{


    /**
     * @return array
     * @throws \Exception
     */
    public function run()
    {


        //$notes = Note::model()->getItemCodes();
        $notes      = Note::model()->getItemInfos();
        $note_codes = [];
        foreach ($notes as $note)
        {
            $note_codes[] = $note->item_code;
        }
        $user_notes = RoleNote::model()->setUser($this->user)->getAccounts($note_codes);
        $list       = [];
        $now_ts     = time();
        foreach ($user_notes as $note_code => $user_note)
        {
            if ($user_note->item_status < Opt::noteStatus_useless)
            {
                list($expires, $rand, $item_code_str, $user_id_str, $sign_str) = explode('#', $user_note->item_value);
                $time_left = intval($expires) - $now_ts;
                if ($time_left > 0)
                {
                    $list[$user_note->item_code]        = $user_note->getOpenInfo();
                    $list[$user_note->item_code]['ttl'] = intval($expires) - $now_ts;
                }

            }
        }
        return ['list' => $list];
    }
}
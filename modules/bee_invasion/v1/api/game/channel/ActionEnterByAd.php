<?php

namespace modules\bee_invasion\v1\api\game\channel;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\ChannelDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\role\RoleNote;
use modules\bee_invasion\v1\model\role\RoleNoteHis;
use modules\bee_invasion\v1\model\user\UserAd;


class ActionEnterByAd extends GameBaseAction
{
    /**
     * @return array
     * @throws \models\common\error\AdvError| \Exception
     */
    public function run()
    {
        $note_code    = $this->inputDataBox->getStringNotNull('ad_note');
        $note_use_for = 'ad_channel_note';
        RoleNote::verifyNoteCode($this->user->id, $note_use_for, $note_code);
        list($expires, $rand, $item_code_str, $user_id_str, $sign_str) = explode('#', $note_code);

        $res = UserAd::model($this->user)->recordWatched($note_use_for, $note_code);
        if ($res === false)
        {
            return $this->dispatcher->createInterruption(AdvError::user_note_has_used['detail'], '广告凭证无效', false, false);
        }


        // $item_code    = $this->inputDataBox->getStringNotNull('item_code');
        $user_account = RoleNote::model()->setUser($this->user)->getAccount($note_use_for);
        $his          = RoleNoteHis::model()->setUserAccountModel($user_account);
        $res          = $his->tryRecord(RoleNoteHis::srcAd2Channel, $note_code, $note_code);
        if ($res === false)
        {
            if (in_array('DuplicateKey', $his->getErrors()))
            {
                return $this->dispatcher->createInterruption(AdvError::user_note_has_used['detail'], '广告凭证无效', false, false);
            }
            else
            {
                return $this->dispatcher->createInterruption('record_error', '记录失败', false, false);
            }
        }

        //$limit_info = $config->getLimitInfo('user_ad_times');
        //$config->increaseValue('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $limit_info[0]]);
        $ttl = ($expires - time());
        return [
            "note_type"         => $note_use_for,
            "current_note_code" => $note_code,
            'verify'            => true,
            'ttl'               => $ttl,
            'userInfoChanged'   => $this->user->getChangedCodes(),
        ];
    }
}
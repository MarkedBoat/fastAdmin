<?php

namespace modules\bee_invasion\v1\api\game\channel;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\tool\BiParam;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\role\RoleNote;
use modules\bee_invasion\v1\model\role\RoleNoteHis;
use modules\bee_invasion\v1\model\user\UserCurrency;


class ActionEnterByTicket extends GameBaseAction
{
    /**
     * @return array
     * @throws \models\common\error\AdvError
     */
    public function run()
    {
        $channel_code = $this->inputDataBox->getStringNotNull('channel_code');
        $channel      = (new Channel())->getItemByCode($channel_code, false);

        $note_item_code = $channel_code . '_note';


        $curr_note_account = RoleNote::model()->setUser($this->user)->getAccount($note_item_code);
        $now_ts            = time();
        $ar                = explode('#', $curr_note_account->item_value);
        $expires           = isset($ar[0]) ? intval($ar[0]) : 0;
        $time_left         = $expires ? ($expires - $now_ts) : 0;
        Sys::app()->addLog(['status' => $curr_note_account->item_value, 'time_left' => $time_left], 'last_note_info');

        if ($curr_note_account->item_status < Opt::noteStatus_useless)
        {
            if ($time_left > 0)
            {
                throw  new AdvError(AdvError::user_lasted_note_is_ok);
            }
        }


        if (!(isset($channel->threshold['pass']['type']) && isset($channel->threshold['pass']['amount']) && is_array($channel->threshold['pass']['type']) && is_int($channel->threshold['pass']['amount']) && $channel->threshold['pass']['amount'] > 0))
        {
            throw  new AdvError(AdvError::data_info_unexpected);
        }
        Sys::app()->addLog([$channel->threshold['pass']], 'pass');
        $payment_item        = (new Currency())->getItemByCode($channel->threshold['pass']['type'][1]);
        $pay_account         = UserCurrency::model()->setUser($this->user)->getAccount($channel->threshold['pass']['type'][1]);
        $user_payment_amount = $pay_account->item_amount;
        $pay_amount          = $channel->threshold['pass']['amount'];
        if (is_array($pay_amount))
        {
            $pay_amount = bcmul($pay_amount[0], pow(10, $pay_amount[1]));
        }
        else
        {
            $pay_amount = $pay_amount * BiParam::dbNumberRate;
        }
        if ($user_payment_amount < $pay_amount)
        {
            throw new AdvError(AdvError::user_money_not_enough_to_pay, '您没有足够的门票，不能进入', ['$user_payment_amount' => $user_payment_amount, '$pay_amount' => $pay_amount]);
        }
        $ttl            = 600;
        $channel_note   = RoleNote::generateNoteCode($this->user->id, $note_item_code, $ttl);
        $pay_his        = (new UserCurrencyHis())->setUserAccountModel($pay_account)->setOperationStep(1);
        $pay_record_res = $pay_his->tryRecord(UserCurrencyHis::srcUsed, $channel_note, $pay_amount);
        if ($pay_record_res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录失败', false, false);
        }

        $note_account = RoleNote::model()->setUser($this->user)->getAccount($note_item_code);
        $note_his     = (new RoleNoteHis())->setUserAccountModel($note_account);


        $note_record_res = $note_his->setOperationStep(1)->tryRecord(RoleNoteHis::srcTicket2Channel, $channel_note, $channel_note);
        if ($note_record_res === false)
        {
            return $this->dispatcher->createInterruption('record_error', '记录票据失败', false, false);
        }
        $pay_his->setOperationStep(3)->update(true, false);

        Sys::app()->addLog([
            '$channel_note'   => $channel_note,
            '$channel'        => $channel->getOuterDataArray(),
            '$pay_record_res' => $pay_record_res
        ], 'record');
        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $this->user->id], time());
        return [
            "note_type"         => $note_item_code,
            "current_note_code" => $channel_note,
            'ttl'               => $ttl,
            'userInfoChanged'   => $this->user->getChangedCodes(),
        ];
    }
}
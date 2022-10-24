<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RoleNoteHisDao;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Note;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\user\TUserOperationHistory;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCg;

class RoleNoteHis extends RoleNoteHisDao
{
    use TUserOperationHistory;

    const src_map = [
        'ad_watched'        => ['val' => 'ad_watched', 'op_type' => 1, 'item_code' => 'ad_note'],
        'ad_to_draw'        => ['val' => 'ad_to_draw', 'op_type' => 1, 'item_code' => 'ad_note', 'is_from_ad' => true],
        'ad_to_channel'     => ['val' => 'ad_to_channel', 'op_type' => 1, 'item_code' => 'ad_channel_note', 'is_from_ad' => true],
        'ticket_to_channel' => ['val' => 'channel_ticket2vip_channel_note', 'op_type' => 1, 'item_code' => 'ticket_to_channel'],
        'order_pay'         => ['val' => 'order_pay', 'op_type' => 2,],
        'order_goods'       => ['val' => 'order_goods', 'op_type' => 1,],
    ];

    const srcAd2Channel     = 'ad_to_channel';
    const srcTicket2Channel = 'ticket_to_channel';
    const srcOrderPay       = 'order_pay';
    const srcOrderGoods     = 'order_goods';


    public function initUserAccountModel()
    {
        $this->userAccountModel = new RoleNote();
    }

}
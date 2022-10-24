<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCg;

class UserCgHis extends UserCgHisDao
{
    use TUserOperationHistory;

    const src_map          = [
        'used'            => ['val' => 1, 'op_type' => 2, 'title' => '消耗'],
        'order_pay'       => ['val' => 'order_pay', 'op_type' => 2, 'title' => '订单支付'],
        'order_goods'     => ['val' => 'order_goods', 'op_type' => 1, 'title' => '订单购买'],
        'lottery_goods'   => ['val' => 'lottery_goods', 'op_type' => 1, 'title' => '抽奖'],
        'lev_up'          => ['val' => 'lev_up', 'op_type' => 1, 'title' => '升级奖励'],
        'lev1_inviter'    => ['val' => 'lev1_inviter', 'op_type' => 1, 'title' => '一级邀请人收益'],
        'lev2_inviter'    => ['val' => 'lev2_inviter', 'op_type' => 1, 'title' => '二级邀请人收益'],
        'ad_lev1_inviter' => ['val' => 'ad_lev1_inviter', 'op_type' => 1, 'title' => '一级邀请人广告收益'],
        'ad_lev2_inviter' => ['val' => 'ad_lev2_inviter', 'op_type' => 1, 'title' => '二级邀请人广告收益'],
        'rank_award'      => ['val' => 'rank_award', 'op_type' => 1, 'title' => '排行榜奖励'],
        'order_apply'     => ['val' => 'order_apply', 'op_type' => 2, 'title' => '提现申请',],
        'sign_award'      => ['val' => 'sign_award', 'op_type' => 1, 'title' => '签到奖励',],
        'counter_sign'    => ['val' => 'counter_sign', 'op_type' => 2, 'title' => '补签扣除',],
        'lottery'         => ['val' => 'lottery', 'op_type' => 1, 'title' => '抽奖',],
        'com_add'         => ['val' => 'com_add', 'op_type' => 1, 'title' => '公司其他项目加的',],
        'teambonus'       => ['val' => 'teambonus', 'op_type' => 1, 'title' => '极差收益',],

    ];
    const srcUsed          = 'used';
    const srcOrderPay      = 'order_pay';
    const srcOrderGoods    = 'order_goods';
    const srcLotteryGoods  = 'lottery_goods';
    const srcOrderApply    = 'order_apply';
    const srcSignAward     = 'sign_award';
    const srcCounterSign   = 'counter_sign';
    const srcLottery       = 'lottery';
    const srcLevUp         = 'lev_up';
    const srcLev1Inviter   = 'lev1_inviter';
    const srcLev2Inviter   = 'lev2_inviter';
    const srcRankAward     = 'rank_award';
    const srcComAdd        = 'com_add';
    const srcAdLev1Inviter = 'ad_lev1_inviter';
    const srcAdLev2Inviter = 'ad_lev2_inviter';
    const srcTeambonus     = 'teambonus';


    public function initUserAccountModel()
    {
        $this->userAccountModel = new UserCg();
    }

    public function getOpenInfo()
    {
        return [
            'id'             => $this->id,
            'src'            => $this->src,
            'src_id'         => $this->src_id,
            'operation'      => self::src_map[$this->src]['title'],
            'user_id'        => $this->user_id,
            'item_code'      => $this->item_code,
            'item_name'      => ConsumableGoods::model()->getItemByCode($this->item_code)->item_name,
            'item_amount'    => $this->item_amount,
            'curr_amount'    => $this->curr_amount,
            'expect_amount'  => $this->expect_amount,
            'src_op_type'    => $this->src_op_type,
            'operation_type' => $this->src_op_type === Opt::operationIncome ? '收入' : '支出',
            'src_open_id'    => $this->src_open_id,
            'src_remark'     => $this->src_remark,
            'create_time'    => $this->create_time,
        ];
    }
}
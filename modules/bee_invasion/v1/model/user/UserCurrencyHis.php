<?php

namespace modules\bee_invasion\v1\model\user;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserCurrencyHisDao;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\TUserOperationHistory;
use modules\bee_invasion\v1\model\user\UserCg;
use modules\bee_invasion\v1\model\user\UserCurrency;


class UserCurrencyHis extends UserCurrencyHisDao
{
    use TUserOperationHistory;
    const src_map              = [
        'used'                => ['val' => 1, 'op_type' => 2, 'title' => '消耗'],
        'order_pay'           => ['val' => 'order_pay', 'op_type' => 2, 'title' => '订单支付'],
        'order_goods'         => ['val' => 'order_goods', 'op_type' => 1, 'title' => '订单购买'],
        'lev_up'              => ['val' => 'lev_up', 'op_type' => 1, 'title' => '升级奖励'],
        'lev1_inviter'        => ['val' => 'lev1_inviter', 'op_type' => 1, 'title' => '一级邀请人收益'],
        'lev2_inviter'        => ['val' => 'lev2_inviter', 'op_type' => 1, 'title' => '二级邀请人收益'],
        'ad_lev1_inviter'     => ['val' => 'ad_lev1_inviter', 'op_type' => 1, 'title' => '一级邀请人广告收益'],
        'ad_lev2_inviter'     => ['val' => 'ad_lev2_inviter', 'op_type' => 1, 'title' => '二级邀请人广告收益'],
        'rank_award'          => ['val' => 'rank_award', 'op_type' => 1, 'title' => '排行榜奖励'],
        'order_apply'         => ['val' => 'order_apply', 'op_type' => 2, 'title' => '提现申请',],
        'sign_award'          => ['val' => 'sign_award', 'op_type' => 1, 'title' => '签到奖励',],
        'counter_sign'        => ['val' => 'counter_sign', 'op_type' => 2, 'title' => '补签扣除',],
        'lottery'             => ['val' => 'lottery', 'op_type' => 1, 'title' => '抽奖',],
        'com_add'             => ['val' => 'com_add', 'op_type' => 1, 'title' => '公司其他项目加的',],
        'agent_pay'           => ['val' => 'agent_pay', 'op_type' => 1, 'title' => '代理人收益',],
        'teambonus'           => ['val' => 'teambonus', 'op_type' => 1, 'title' => '极差收益',],
        'admin_add'           => ['val' => 'admin_add', 'op_type' => 1, 'title' => '管理员添加',],
        'admin_cutdown'       => ['val' => 'admin_cutdown', 'op_type' => 2, 'title' => '管理员扣除',],
        'error_data_cutdown'  => ['val' => 'error_data_cutdown', 'op_type' => 2, 'title' => '扣除错误添加的数据',],
        'error_data_rollback' => ['val' => 'error_data_rollback', 'op_type' => 2, 'title' => '错误数据撤回',],

    ];
    const srcUsed              = 'used';
    const srcOrderPay          = 'order_pay';
    const srcOrderGoods        = 'order_goods';
    const srcOrderApply        = 'order_apply';
    const srcSignAward         = 'sign_award';
    const srcCounterSign       = 'counter_sign';
    const srcLottery           = 'lottery';
    const srcLevUp             = 'lev_up';
    const srcLev1Inviter       = 'lev1_inviter';
    const srcLev2Inviter       = 'lev2_inviter';
    const srcRankAward         = 'rank_award';
    const srcComAdd            = 'com_add';
    const srcAdLev1Inviter     = 'ad_lev1_inviter';
    const srcAdLev2Inviter     = 'ad_lev2_inviter';
    const srcAgent_pay         = 'agent_pay';
    const srcTeambonus         = 'teambonus';
    const srcAdminAdd          = 'admin_add';
    const srcAdminCutdown      = 'admin_cutdown';
    const srcErrorDataCutdown  = 'error_data_cutdown';
    const srcErrorDataRollback = 'error_data_rollback';


    public function initUserAccountModel()
    {
        $this->userAccountModel = UserCurrency::model();
    }

    public function getHis($type, $item_code, $page_size, $page_index)
    {
        $page_index = ($page_index < 1 ? 1 : $page_index) - 1;
        $start      = $page_index * $page_size;
        return $this->setLimit($start, $page_size)->addSort('id', 'desc')->findAllByWhere([
            'op_type'   => $type,
            'item_code' => $item_code,
        ]);
    }

    public function getOpenInfo()
    {
        return [
            'id'             => $this->id,
            'src'            => $this->src,
            'src_id'         => $this->src_id,
            'operation'      => self::src_map[$this->src]['title'] . ($this->is_rollback === Opt::YES ? '(错误数据，金额已作废，记录保留)' : ''),
            'user_id'        => $this->user_id,
            'item_code'      => $this->item_code,
            'item_name'      => Currency::model()->getItemByCode($this->item_code)->item_name,
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
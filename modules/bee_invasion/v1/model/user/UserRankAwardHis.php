<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserObjectHisDao;
use modules\bee_invasion\v1\dao\user\UserRankAwardHisDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCg;

class UserRankAwardHis extends UserRankAwardHisDao
{


    const status_map          = [
        1 => ['val' => 'wait_postal_info', 'title' => '等待填写地址'],
        3 => ['val' => 'info_check', 'title' => '等待受理'],
        4 => ['val' => 'info_fixed', 'title' => '收获信息修改了'],
        7 => ['val' => 'ok', 'title' => '完成'],
        8 => ['val' => 'expired', 'title' => '过期'],
    ];
    const stepWaitPostalInfo  = 1;
    const stepWaitInfoCheck   = 3;
    const stepPostalInfoFixed = 4;
    const stepOk              = 7;

    /** @var $his_dao null|UserCgHisDao|UserCurrencyHisDao|UserObjectHisDao */
    private $his_dao;


    /**
     * @param RankTop $rankTop
     * @return static
     */
    public function setRank(RankTop $rankTop)
    {
        $this->rank_sn      = $rankTop->rank_sn;
        $this->channel_code = $rankTop->channel_code;
        $this->date_index   = $rankTop->date_index;
        $this->user_id      = $rankTop->user_id;
        $this->is_fake      = $rankTop->is_fake;
        return $this;
    }

    /**
     * @param int $award_sn
     * @param string $item_class
     * @param string $item_code
     * @param int $item_amount
     * @param string $award_detail
     * @return static
     */
    public function setItem($award_sn, $item_class, $item_code, $item_amount, $award_detail)
    {
        $this->award_sn     = $award_sn;
        $this->item_code    = $item_code;
        $this->item_class   = $item_class;
        $this->item_amount  = $item_amount;
        $this->award_detail = $award_detail;
        return $this;
    }

    /**
     * @param UserCgHisDao|UserCurrencyHisDao|UserObjectHisDao $dao
     * @return $this
     */
    public function setHisDao($dao)
    {
        $this->his_dao = $dao;
        return $this;
    }

    public function getOpenInfo()
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'channel_code'  => $this->channel_code,
            'channel_name'  => Channel::model()->getItemByCode($this->channel_code)->item_name,
            'rank_sn'       => $this->rank_sn,
            'award_sn'      => $this->award_sn,
            'item_class'    => $this->item_class,
            'item_code'     => $this->item_code,
            'award_detail'  => $this->award_detail,
            'item_amount'   => $this->item_amount,
            'his_id'        => $this->his_id,
            'his_item'      => empty($this->his_dao) ? false : $this->his_dao->getOpenInfo(),
            'status'        => self::status_map[$this->award_status]['title'],
            'status_detail' => self::status_map[$this->award_status]['val'],
            'create_time'   => $this->create_time,
        ];
    }
}
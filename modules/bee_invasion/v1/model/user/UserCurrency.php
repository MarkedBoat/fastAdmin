<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\user\User;

class UserCurrency extends UserCurrencyDao
{
    use TUserAccount;

    protected $valueType  = 'amount';
    protected $valueField = 'item_amount';//db table存储值的字段

    const cacheConfigKey_accountInfo = 'UserCurrencyAccountInfo';

    private static $account_info_map = [];

    const src_map = [];


    public function initItemModel()
    {
        $this->itemModel = new Currency();
    }

    public function getUserChangeCodes()
    {
        return User::currency_changed;
    }

    public function addPoints($payed_amount, $pay_src_flag, $pay_src_id)
    {
        if ($this->item_code === 'gold_ingot')
        {
            $points_account = UserCurrency::model()->setUser($this->user)->getAccount('points');
            $rate           = Config::model()->getItemByCode('rate_4_gold_ingot2points')->setting['rate'];
            $points         = $rate[0] * $payed_amount / pow(10, $rate[1]);
            Sys::app()->addLog([$payed_amount, $rate, $points], 'addPoints');

            $points_his        = (new UserCurrencyHis())->setUserAccountModel($points_account)->setOperationStep(1);
            $points_record_res = $points_his->tryRecord($pay_src_flag, $pay_src_id, $points);
            $this->user->checkLevelUp($points_account);
        }
    }

}
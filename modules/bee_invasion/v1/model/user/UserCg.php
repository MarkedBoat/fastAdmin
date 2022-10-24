<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\user\User;

class UserCg extends UserCgDao
{
    use TUserAccount;

    protected $valueType  = 'amount';
    protected $valueField = 'item_amount';//db table存储值的字段

    const cacheConfigKey_accountInfo = 'UserCgAccountInfo';
    private static $account_info_map = [];


    const src_map = [];


    public function initItemModel()
    {
        $this->itemModel = new ConsumableGoods();
    }

    /**
     * @return string
     */
    public function getUserChangeCodes()
    {
        return User::cg_changed;
    }


}
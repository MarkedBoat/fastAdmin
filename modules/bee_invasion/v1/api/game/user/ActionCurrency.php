<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyDao;
use modules\bee_invasion\v1\dao\user\UserDataStatisDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\UserDataStatis;


class ActionCurrency extends GameBaseAction
{
    public function run()
    {
        $list           = [];
        $user_curr_daos = UserCurrencyDao::model()->findAllByWhere(['user_id' => $this->user->id]);
        $statis_daos    = UserDataStatis::model()->findAllByWhere(['user_id' => $this->user->id]);
        /** @var  $statis_map UserDataStatis[] */
        $statis_map = [];
        foreach ($statis_daos as $statis_dao)
        {
            $statis_map[$statis_dao->item_code] = $statis_dao;
        }
        if (count($user_curr_daos))
        {
            $amount_map = [];
            foreach ($user_curr_daos as $user_curr_dao)
            {
                $amount_map[$user_curr_dao->item_code] = intval($user_curr_dao->item_amount);
            }

            $currencys = (new Currency())->getItemInfos();


            foreach ($currencys as $i => $currency)
            {
                $tmp_ar               = $currency->getOpenInfo();
                $tmp_ar['amount']     = isset($amount_map[$currency->item_code]) ? $amount_map[$currency->item_code] : 0;
                $tmp_ar['addup']      = 0;
                $tmp_ar['last_value'] = 0;
                $tmp_ar['max_value']  = 0;
                if ($statis_map[$currency->item_code])
                {
                    $tmp_ar['addup']      = $statis_map[$currency->item_code]->item_addup;
                    $tmp_ar['last_value'] = $statis_map[$currency->item_code]->item_value;
                    $tmp_ar['max_value']  = $statis_map[$currency->item_code]->item_max;
                }

                $list[$currency->item_code] = $tmp_ar;

            }
        }

        return ['list' => $list];

    }
}
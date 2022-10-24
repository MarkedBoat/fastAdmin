<?php

namespace modules\bee_invasion\v1\api\game\economy;

use models\common\ActionBase;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\order\CurrencyDao;
use modules\bee_invasion\v1\model\economy\Currency;


class ActionCurrencys extends ActionBase
{
    public function run()
    {
        return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new Currency())->getItemInfos()))];
    }
}
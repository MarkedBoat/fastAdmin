<?php

namespace modules\bee_invasion\v1\api\game\perk;

use models\common\ActionBase;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\model\play\Perk;


class ActionPerks extends ActionBase
{
    public function run()
    {
        return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new Perk())->getItemInfos()))];
    }
}
<?php

namespace modules\bee_invasion\v1\api\game\equipment;

use models\common\ActionBase;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\model\play\Equipment;


class ActionEquipments extends ActionBase
{
    public function run()
    {
        return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new Equipment())->getItemInfos()))];

    }
}
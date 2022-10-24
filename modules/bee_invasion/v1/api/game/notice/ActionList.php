<?php

namespace modules\bee_invasion\v1\api\game\notice;

use models\common\ActionBase;
use modules\bee_invasion\v1\model\notice\Notice;


class ActionList extends ActionBase
{
    public function run()
    {
        return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new Notice())->getLastedModels()))];
    }
}
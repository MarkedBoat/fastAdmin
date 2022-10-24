<?php

namespace modules\bee_invasion\v1\api\game\cg;

use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;


class ActionCgs extends GameBaseAction
{
    public function run()
    {
        return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new ConsumableGoods())->getItemInfos()))];
    }
}
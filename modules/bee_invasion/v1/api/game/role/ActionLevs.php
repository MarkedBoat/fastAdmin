<?php

namespace modules\bee_invasion\v1\api\game\role;

use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;


class ActionLevs extends GameBaseAction
{


    public function run()
    {
        $list   = [];
        $models = RoleLevCfgDao::model()->findAllByWhere(['is_ok' => Opt::isOk]);
        foreach ($models as $model)
        {
            $list[] = $model->getOpenInfo();
        }

        return ['list' => $list];

    }
}
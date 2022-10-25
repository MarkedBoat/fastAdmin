<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;


class ActionActions extends AdminBaseAction
{
    public function run()
    {
        return RbacAction::model()->findAllByWhere(['is_ok' => Opt::isOk]);
    }


}
<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\rbac\RbacAction;


class ActionActions extends AdminBaseAction
{
    public function run()
    {
        return RbacAction::model()->findAllByWhere(['is_ok' => Opt::isOk]);
    }


}
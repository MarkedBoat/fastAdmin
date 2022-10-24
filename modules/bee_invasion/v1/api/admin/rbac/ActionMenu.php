<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacMenu;


class ActionMenu extends AdminBaseAction
{
    public function run()
    {
        return RbacMenu::model()->getMenusTree($this->inputDataBox->tryGetString('all') === 'yes');
    }


}
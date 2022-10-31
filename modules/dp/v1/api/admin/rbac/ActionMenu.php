<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\admin\rbac\RbacMenu;


class ActionMenu extends AdminBaseAction
{
    public function run()
    {
        return RbacMenu::model()->getMenusTree($this->inputDataBox->tryGetString('all') === 'yes');
    }


}
<?php

namespace modules\_dp\v1\api\rbac;


use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\model\rbac\RbacMenu;
use modules\_dp\v1\model\rbac\RbacRole;
use modules\_dp\v1\model\rbac\RbacRoleMenu;

class ActionRender_config extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeHtml);
        return $this->renderTpls(['/modules/_dp/v1/view/rbac/rbac.html'], []);

    }
}
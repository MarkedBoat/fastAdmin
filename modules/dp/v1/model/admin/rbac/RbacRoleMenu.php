<?php

namespace modules\dp\v1\model\admin\rbac;


use models\common\opt\Opt;
use modules\dp\v1\dao\admin\AdminDao;
use modules\dp\v1\dao\admin\rbac\RbacActionDao;
use modules\dp\v1\dao\admin\rbac\RbacRoleMenuDao;
use modules\dp\v1\dao\game\RoleDao;
use modules\dp\v1\dao\game\RoleLevCfgDao;
use modules\dp\v1\dao\user\UserCgHisDao;
use modules\dp\v1\dao\user\UserDao;
use modules\dp\v1\dao\user\UserInviterDao;
use modules\dp\v1\model\TCache;
use modules\dp\v1\model\TInfo;

class RbacRoleMenu extends RbacRoleMenuDao
{
    use TInfo;
}
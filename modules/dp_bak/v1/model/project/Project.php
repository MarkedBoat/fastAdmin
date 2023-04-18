<?php

namespace modules\dp\v1\model\project;


use models\common\opt\Opt;
use modules\dp\v1\dao\admin\AdminDao;
use modules\dp\v1\dao\game\RoleDao;
use modules\dp\v1\dao\game\RoleLevCfgDao;
use modules\dp\v1\dao\project\ProjectDao;
use modules\dp\v1\dao\user\UserCgHisDao;
use modules\dp\v1\dao\user\UserDao;
use modules\dp\v1\dao\user\UserInviterDao;
use modules\dp\v1\model\admin\rbac\RbacRole;
use modules\dp\v1\model\admin\rbac\RbacUserRole;
use modules\dp\v1\model\TCache;
use modules\dp\v1\model\TInfo;

class Project extends ProjectDao
{

}
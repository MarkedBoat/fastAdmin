<?php

namespace modules\bee_invasion\v1\model\admin\dbdata;


use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\admin\AdminDao;
use modules\bee_invasion\v1\dao\admin\dbdata\DbRelationDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacActionDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;

class DbRelation extends DbRelationDao
{
    use TInfo;
}
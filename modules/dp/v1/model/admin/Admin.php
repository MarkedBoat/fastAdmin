<?php

namespace modules\dp\v1\model\admin;


use models\common\opt\Opt;
use modules\dp\v1\dao\admin\AdminDao;
use modules\dp\v1\dao\game\RoleDao;
use modules\dp\v1\dao\game\RoleLevCfgDao;
use modules\dp\v1\dao\user\UserCgHisDao;
use modules\dp\v1\dao\user\UserDao;
use modules\dp\v1\dao\user\UserInviterDao;
use modules\dp\v1\model\admin\rbac\RbacRole;
use modules\dp\v1\model\admin\rbac\RbacUserRole;
use modules\dp\v1\model\TCache;
use modules\dp\v1\model\TInfo;

class Admin extends AdminDao
{
    use TInfo;

    public $roles      = [];
    public $role_codes = [];

    private $is_init_roles = false;

    /**
     * @return static
     * @throws \Exception
     */
    public function initRoles()
    {
        if ($this->is_init_roles === false)
        {
            $daos = RbacUserRole::model()->findAllByWhere(['user_id' => $this->id, 'is_ok' => Opt::isOk]);
            if (count($daos))
            {
                $role_ids = [];
                foreach ($daos as $dao)
                {
                    $role_ids[] = $dao->role_id;
                }
                $role_daos = RbacRole::model()->findAllByWhere(['id' => $role_ids]);
                foreach ($role_daos as $role_dao)
                {
                    $this->roles[]      = $role_dao;
                    $this->role_codes[] = $role_dao->role_code;
                }
            }
        }
        return $this;
    }

    public function getOpenInfo()
    {
        return [
            'user_id'   => $this->id,
            'real_name' => $this->real_name,
        ];
    }
}
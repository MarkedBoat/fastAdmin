<?php

namespace modules\dp\v1\model\admin\rbac;


use models\common\opt\Opt;
use modules\dp\v1\dao\admin\AdminDao;
use modules\dp\v1\dao\admin\rbac\RbacActionDao;
use modules\dp\v1\dao\game\RoleDao;
use modules\dp\v1\dao\game\RoleLevCfgDao;
use modules\dp\v1\dao\user\UserCgHisDao;
use modules\dp\v1\dao\user\UserDao;
use modules\dp\v1\dao\user\UserInviterDao;
use modules\dp\v1\model\TCache;
use modules\dp\v1\model\TInfo;

class RbacAction extends RbacActionDao
{
    use TInfo;

    private $role_codes = [];


    /**
     * @param $uri
     * @return static
     * @throws \models\common\error\AdvError
     */
    public function getByUri($uri)
    {
        $model = $this->findOneByWhere(['action_uri' => $uri], false);
        if (empty($model))
        {
            $this->action_uri  = $uri;
            $this->action_name = md5($uri);
            $this->action_code = $this->action_name;
            $this->insert(false);
            $model = $this->findOneByWhere(['action_uri' => $uri]);
        }
        $model->initRolesInfo();
        return $model;

    }

    public function initRolesInfo()
    {
        $action_tn      = $this->getTableName();
        $task_action_tn = RbacTaskAction::model()->getTableName();
        $role_task_tn   = RbacRoleTask::model()->getTableName();
        $role_tn        = RbacRole::model()->getTableName();
        $sql            = "select a.id,a.action_code,a.action_name,a.action_uri,t_a.is_ok,r_t.is_ok, r.* from {$action_tn} as a left join {$task_action_tn} as t_a on t_a.action_id=a.id left join {$role_task_tn} as r_t on r_t.task_id=t_a.task_id left join {$role_tn} as r on r_t.role_id=r.id
where a.id={$this->id} and a.is_ok=1 and t_a.is_ok=1 and r_t.is_ok=1 and r.is_ok=1;";
        $rows           = $this->getDbConnect()->setText($sql)->queryAll();
        foreach ($rows as $row)
        {
            $this->role_codes[] = $row['role_code'];
        }

    }

    public function getRoleCodes()
    {
        return $this->role_codes;
    }


}
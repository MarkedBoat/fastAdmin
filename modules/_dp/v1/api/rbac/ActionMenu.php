<?php

namespace modules\_dp\v1\api\rbac;


use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\model\rbac\RbacMenu;
use modules\_dp\v1\model\rbac\RbacRole;
use modules\_dp\v1\model\rbac\RbacRoleMenu;

class ActionMenu extends AdminBaseAction
{
    public function run()
    {
        return $this->getMenusTree();
    }


    public function getMenusTree()
    {
        $ar     = [];
        $pid_ar = [];

        $menu_role_daos = RbacRoleMenu::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::isOk]);
        $user_roles_ids = array_map(function ($role_dao) { return $role_dao->id; }, $this->user->roles);
        $menu_map       = [];
        $is_super_user  = in_array(RbacRole::superAdmin, $this->user->role_codes, true);
        foreach ($menu_role_daos as $menu_role_dao)
        {
            if (!isset($menu_map[$menu_role_dao->menu_id]))
            {
                $menu_map[$menu_role_dao->menu_id] = false;
            }
            if (in_array($menu_role_dao->role_id, $user_roles_ids))
            {
                $menu_map[$menu_role_dao->menu_id] = true;
            }
        }
        Sys::app()->addLog($menu_map, '$menu_map');

        $models = RbacMenu::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::isOk]);
        foreach ($models as $model)
        {
            if (!($is_super_user || !isset($menu_map[$model->id]) || $menu_map[$model->id]))
            {
                continue;
            }
            $pid = intval($model->pid);
            if (!isset($pid_ar[$pid]))
            {
                $pid_ar[$pid] = [];
            }
            $pid_ar[$pid][] = $model;
            if ($pid === 0)
            {
                $ar[] = $model;
            }
        }

        foreach ($ar as $i => $model)
        {
            self::menuTree($pid_ar, $ar[$i]);
        }

        return $ar;
    }

    public static function menuTree($map, RbacMenu &$obj)
    {
        $id = intval($obj->id);
        if (isset($map[$id]))
        {
            foreach ($map[$id] as $sub_model)
            {
                self::menuTree($map, $sub_model);
                $obj->sub_menus[] = $sub_model;
            }
        }
    }

}
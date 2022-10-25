<?php

namespace modules\bee_invasion\v1\model\admin\rbac;


use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\admin\AdminDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacActionDao;
use modules\bee_invasion\v1\dao\admin\rbac\RbacMenuDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;

class RbacMenu extends RbacMenuDao
{
    use TInfo;

    public $sub_menus = [];


    /**
     * @param bool $all
     * @return array 是个object 树
     */
    public function getMenusTree($all = false)
    {
        $ar     = [];
        $pid_ar = [];

        $models = $all ? $this->setLimit(0, 1000)->findAllByWhere(['is_ok' => [Opt::isOk, Opt::isNotOk]]) : $this->findAllByWhere(['is_ok' => Opt::isOk]);
        foreach ($models as $model)
        {
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

    /**
     * 参数必须是 getMenusTree 的结果
     * @param $menusTree
     * @return array
     */
    public static function menusObjctTreeAsArray($menusTree)
    {
        $ar = [];
        foreach ($menusTree as $i => $model)
        {
            $ar[] = $model->getOpenInfo();
        }
        return $ar;
    }

    public function getOpenInfo($init = true)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'pid'        => $this->pid,
            'url'        => $this->url,
            'opts'       => $this->getJsondecodedValue($this->opts, 'object'),
            'remark'     => $this->remark,
            'is_backend' => $this->is_backend,
            'subs'       => $init ? (count($this->sub_menus) > 0 ? array_map(function ($sub_menu) { return $sub_menu->getOpenInfo(); }, $this->sub_menus) : []) : [],
        ];
    }


}
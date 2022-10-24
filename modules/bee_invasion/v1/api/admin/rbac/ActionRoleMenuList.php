<?php

namespace modules\bee_invasion\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionRoleMenuList extends AdminBaseAction
{
    public function run()
    {
        $data['id'] = $this->inputDataBox->getStringNotNull('id');
        $tree = [];
        $items = Sys::app()->db('dev')->setText("select r_t.id, t.*,'role_menu' as tname from bi_bg_rbac_role r left join bi_bg_rbac_role_menu r_t on r.id = r_t.role_id left join bi_bg_rbac_menu t on t.id = r_t.menu_id  where r.id = :id and r.is_ok = 1 and r_t.is_ok = 1 and t.is_ok = 1")->bindArray($data)->queryAll();
        if($items){
            $items = array_column($items, null, 'id');

            foreach ($items as $item){
                if (isset($items[$item['pid']])){
                    $items[$item['pid']]['son'][] = &$items[$item['id']];
                }else{
                    $tree[] = &$items[$item['id']];
                }
            }
        }

        return $tree;



    }

}
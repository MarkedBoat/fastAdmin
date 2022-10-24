<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Equipment;


class ActionEquipments extends GameBaseAction
{


    /**
     * @return array
     * @throws \Exception
     */
    public function run()
    {


        $role_eq_daos = RoleEquipmentDao::model()->findAllByWhere(['user_id' => $this->user->id]);
        $list         = [];
        if (count($role_eq_daos))
        {
            $item_codes = [];
            $data       = [];
            foreach ($role_eq_daos as $role_eq_dao)
            {
                if ($role_eq_dao->item_amount > 0)
                {
                    $item_codes[] = $role_eq_dao->item_code;
                }
            }
            Sys::app()->addLog($item_codes);

            $eq_infos = (new Equipment())->getItemInfos();

            Sys::app()->addLog($eq_infos);

            foreach ($eq_infos as $eq_info)
            {
                if (in_array($eq_info->item_code, $item_codes, true))
                {
                    $list[] = $eq_info->getOpenInfo();
                }
            }
        }


        return ['list' => $list];

    }
}
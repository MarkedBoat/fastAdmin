<?php

namespace modules\bee_invasion\v1\api\game\role;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RolePorcessDao;
use modules\bee_invasion\v1\dao\game\role\RoleStatisDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\role\RoleArm;


class ActionInfo extends GameBaseAction
{


    public function run()
    {


        $role_dao = RoleDao::model()->findOneByWhere(['user_id' => $this->user->id], false);
        if (empty($role_dao))
        {
            $role_dao              = new  RoleDao();
            $role_dao->user_id     = $this->user->id;
            $role_dao->update_time = date('Y-m-d H:i:s', time());
            $role_dao->lev         = 0;
            $role_dao->insert();
        }
        $base_info = [
            'user_id'   => $this->user->id,
            'nickname'  => $this->user->nickname,
            'stage_max' => 0,
            'level'     => intval($role_dao->lev),
            'hp'        => 3,
            'def'       => 1,
            'hp_max'    => 100,
            'atk_max'   => 100,
            'arm'       => [
                'armor'  => [
                    "id"          => 0,
                    "item_name"   => "无",
                    "item_code"   => "empty",
                    "item_icon"   => "",
                    "item_detail" => "默认的空",
                    "has_ui"      => 0,
                    "threshold"   => [],
                    "effect"      => [
                        "role" => [
                            "atk"   => [
                                "duration" => 0,
                                "type"     => "+",
                                "value"    => 0
                            ],
                            "hp"    => [
                                "duration" => 0,
                                "type"     => "+",
                                "value"    => 0
                            ],
                            "perks" => []
                        ]
                    ]
                ],
                'weapon' => [
                    'bullet' => [
                        "fireRate" => 2,
                        "atk"      => 1,
                        "guns"     => 1
                    ]
                ],
            ],
        ];

        if ($role_dao->lev)
        {
            $lev_dao                                     = RoleLevCfgDao::model()->findOneByWhere(['lev' => $role_dao->lev], false);
            $base_info['hp']                             = intval($lev_dao->base_hp);
            $base_info['arm']['weapon']['bullet']['atk'] = intval($lev_dao->base_dmg);
        }

        $statis_dao = RoleStatisDao::model()->findOneByWhere(['id' => $this->user->id], false);
        if (!empty($statis_dao))
        {
            $base_info['stage_max'] = intval($statis_dao->stage_index);
        }

        $armed_info = RoleArm::model()->getUserArmedInfo($this->user);
        if (!empty($armed_info) && isset($armed_info['armed']['armor']) && !empty($armed_info['armed']['armor']))
        {
            Sys::app()->addLog($armed_info, ' $armed_info');
            $armor_info                = (new Equipment())->getItemByCode($armed_info['armed']['armor']);
            $base_info['arm']['armor'] = $armor_info->getOpenInfo();
        }


        return $base_info;

    }
}
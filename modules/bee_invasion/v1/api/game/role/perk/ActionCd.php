<?php

namespace modules\bee_invasion\v1\api\game\role\perk;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RolePorcessDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;


class ActionCd extends GameBaseAction
{
    

    public function run()
    {


        $role_dao = RoleDao::model()->findOneByWhere(['user_id' => $this->user->id]);

        $base_info                                   = [
            'stage_max' => 0,
            'level'     => intval($role_dao->lev),
            'hp'        => 1,
            'def'       => 1,
            'arm'       => [
                'armor'  => [
                    "id"          => 0,
                    "item_name"   => "无",
                    "item_code"   => "none",
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
        $base_info['equipments']                     = [];
        $base_info['goods']                          = [];
        $base_info['xx']                             = [];
        $lev_dao                                     = RoleLevCfgDao::model()->findOneByWhere(['lev' => $role_dao->lev]);
        $base_info['hp']                             = intval($lev_dao->base_hp);
        $base_info['arm']['weapon']['bullet']['atk'] = intval($lev_dao->base_dmg);

        $process_dao = RolePorcessDao::model()->findByPk($role_dao->id);
        if (!empty($process_dao))
        {
            $base_info['stage_max'] = intval($process_dao->stage_index);
        }

        $armed_dao = RoleArmDao::model()->findByPk($role_dao->id);

        $base_info['xx'][]   = $armed_dao->getOpenInfo();
        $perks               = [];
        $role_equipment_daos = RoleEquipmentDao::model()->findAllByWhere(['role_id' => $role_dao->id]);
        if (count($role_equipment_daos))
        {
            $eids = array_map(function ($model) { return $model->equipment_id; }, $role_equipment_daos);

            $equipment_daos = EquipmentDao::model()->findAllByWhere(['id' => $eids]);
            //var_dump($role_dao->id,$eids,$equipment_daos);die;

            // $role_attr['equipments'] = array_map(function ($dao) { return $dao->getOpenInfo(); }, $equipment_daos);
            $equipment_map = [];
            foreach ($equipment_daos as $equipment_dao)
            {

                $equipment_info                           = $equipment_dao->getOpenInfo();
                $equipment_map[$equipment_dao->item_code] = $equipment_info;
                $base_info['equipments'][]                = $equipment_info;
                if (isset($equipment_info['effect']['perks']))
                {
                    $perks = array_merge($perks, $equipment_info['effect']['perks']);
                }

            }

            if ($armed_dao)
            {
                $armed_open_info = $armed_dao->getOpenInfo();
                if (isset($armed_open_info['armed']['armor']) && isset($equipment_map[$armed_open_info['armed']['armor']]))
                {
                    $base_info['arm']['armor'] = $equipment_map[$armed_open_info['armed']['armor']];
                }
                if (isset($armed_open_info['armed']['weapon']))
                {

                }
            }

        }
        $user_cg_daos = UserCgDao::model()->findAllByWhere(['user_id' => $this->user->id]);

        if (count($user_cg_daos))
        {
            //$item_ids = array_map(function ($model) { return $model->item_id; }, $user_cg_daos);
            //$role_attr['consumables'] = array_map(function ($dao) { return $dao->getOpenInfo(); }, $cg_daos);
            $item_ids   = [];
            $amount_map = [];
            foreach ($user_cg_daos as $user_cg_dao)
            {
                $item_ids[]                        = intval($user_cg_dao->item_id);
                $amount_map[$user_cg_dao->item_id] = intval($user_cg_dao->item_amount);
            }

            $cg_daos = CgDao::model()->findAllByWhere(['id' => $item_ids]);

            foreach ($cg_daos as $cg_dao)
            {
                $cg_info              = $cg_dao->getOpenInfo();
                $cg_info['amount']    = $amount_map[$cg_dao->id];
                $base_info['goods'][] = $cg_info;
                if (isset($cg_info['effect']['perks']))
                {
                    $perks = array_merge($perks, $cg_info['effect']['perks']);
                }
            }
        }


        if (count($perks))
        {
            //  $perk_daos          = PerkDao::model()->findAllByWhere(['item_code' => $perks]);
            // $role_attr['perks'] = array_map(function ($dao) { return $dao->getOpenInfo(); }, $perk_daos);

        }


        return $base_info;

    }
}
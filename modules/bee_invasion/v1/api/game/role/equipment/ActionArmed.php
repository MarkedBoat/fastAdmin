<?php

namespace modules\bee_invasion\v1\api\game\role\equipment;

use models\common\error\AdvError;
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
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\role\RoleArm;
use modules\bee_invasion\v1\model\role\RoleEquipment;


class ActionArmed extends GameBaseAction
{


    public function run()
    {
        $item_code = $this->inputDataBox->getString('item_code');

        if ($item_code === '')
        {
            RoleArm::model()->unarmedEquipment($this->user, 'armor');
        }
        else
        {
            $equipment      = (new Equipment())->getItemByCode($item_code);
            $role_equipment = RoleEquipment::model()->setUser($this->user)->getAccount($item_code);
            if ($role_equipment->item_amount < 1)
            {
                throw new AdvError(AdvError::user_equipment_not_exist);
            }

            RoleArm::model()->armedEquipment($this->user, $equipment);
        }

        $armed_info = RoleArm::model()->getUserArmedInfo($this->user, true);

        return [
            'armed'           => $armed_info['armed'],
            'userInfoChanged' => $this->user->getChangedCodes(),
        ];
    }
}
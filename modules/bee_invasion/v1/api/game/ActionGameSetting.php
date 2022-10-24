<?php

namespace modules\bee_invasion\v1\api\game;

use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;


class ActionGameSetting extends ActionBase
{


    public function run()
    {

        return [
            'stage' => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/stage.json'), true),
            'enemy' => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/enemy.json'), true),
            'role'  => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/role.json'), true),
            'armor' => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/armor.json'), true),
            'perks' => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/perks.json'), true),
            'goods' => json_decode(file_get_contents(__ROOT_DIR__ . '/config/game_define/goods.json'), true),
        ];


    }
}
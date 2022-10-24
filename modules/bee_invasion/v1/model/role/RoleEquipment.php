<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentDao;
use modules\bee_invasion\v1\dao\game\role\RoleNoteDao;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Note;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\user\TUserAccount;
use modules\bee_invasion\v1\model\user\User;

class RoleEquipment extends RoleEquipmentDao
{
    use TUserAccount;


    protected $valueType = 'amount';

    const cacheConfigKey_value       = 'RoleEquipemtCurrent';
    const cacheConfigKey_accountInfo = 'RoleEquipemtAccountInfo';


    private static $account_info_map = [];


    public function initItemModel()
    {
        $this->itemModel = new Equipment();
    }

    public function getUserChangeCodes()
    {
        return User::equipment_changed;
    }


    public function getOpenInfo()
    {
        return [
            'id'               => $this->id,
            'role_id'          => $this->user_id,
            'equipment_amount' => $this->item_amount,
            'equipment_code'   => $this->item_code,
        ];
    }
}
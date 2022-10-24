<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;
use modules\bee_invasion\v1\model\user\User;

class RoleArm extends RoleArmDao
{
    use TItem;

    const cacheConfigInfoKey = 'RoleArm';

    private static $user_arm_map = [];

    /**
     * @return static
     */
    public static function model()
    {
        return new static();
    }


    /**
     * @param User $user
     * @param bool $force_flush
     * @return array|false
     * @throws AdvError
     */
    public function getUserArmedInfo(User $user, $force_flush = false)
    {
        if (isset(self::$user_arm_map[$user->id]) && $force_flush === false)
        {
            return self::$user_arm_map[$user->id];
        }
        $res = $force_flush ? [] : $this->getCache(self::cacheConfigInfoKey, ['user_id' => $user->id]);

        if ($force_flush || empty($res))
        {// 有没有key 和   val 是空值 是两码事
            $dao = RoleArmDao::model()->findOneByWhere(['id' => $user->id], false);
            if (empty($dao))
            {
                $dao              = new RoleArmDao();
                $dao->id          = $user->id;
                $dao->armed       = '{}';
                $dao->update_time = date('Y-m-d H:i:s', time());
                $dao->insert(false,false);
                return false;
            }
            self::$user_arm_map[$user->id] = $dao->getOpenInfo();
            $this->setCache(self::cacheConfigInfoKey, ['user_id' => $user->id], self::$user_arm_map[$user->id]);
        }
        else
        {
            self::$user_arm_map[$user->id] = $res;
        }
        return self::$user_arm_map[$user->id];

    }

    public function armedEquipment(User $user, Equipment $equipment)
    {
        Sys::app()->addLog($equipment->getOpenInfo(), '$equipment_open_info');
        if ($equipment->item_class[0] === 'armor')
        {
            $dao = RoleArmDao::model();
            $tn  = $dao->getTableName();
            $dao->getDbConnect()->setText("update {$tn} set armed = JSON_SET(armed, '$.armor', :item_code) where id=:id")->bindArray([
                ':item_code' => $equipment->item_code,
                ':id'        => $user->id,
            ])->execute();
        }
        else
        {
            throw new AdvError(AdvError::data_info_unexpected, false, $equipment->getOpenInfo());
        }
    }

    /**
     * 解除武装
     * @param User $user
     * @param $equipment_code
     * @throws AdvError
     */
    public function unarmedEquipment(User $user, $equipment_code)
    {
        Sys::app()->addLog($equipment_code, '$equipment_code');
        if ($equipment_code === 'armor')
        {
            $dao = RoleArmDao::model();
            $tn  = $dao->getTableName();
            $dao->getDbConnect()->setText("update {$tn} set armed = JSON_SET(armed, '$.armor', :item_code) where id=:id")->bindArray([
                ':item_code' => '',
                ':id'        => $user->id,
            ])->execute();
        }
        else
        {
            throw new AdvError(AdvError::data_info_unexpected, false, 'unarmedEquipment');
        }
    }
}
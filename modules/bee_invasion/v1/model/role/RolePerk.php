<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\user\User;

class RolePerk
{
    /**
     * @return static
     */
    public static function model()
    {
        return new static();
    }

    public static function getCacheKey()
    {
        return Sys::app()->params['cache_cfg']['RolePerkTimes']['key'];
    }

    /**
     * @param User $user
     * @param $item_code
     * @param bool $force_flush
     * @return int
     * @throws AdvError
     */
    public static function getGoodsAmount(User $user, $item_code, $force_flush = false)
    {
        if (!in_array($item_code, (new Perk())->getItemCodes(), true))
        {
            throw new AdvError(AdvError::res_not_exist, "不存在道具:[{$item_code}]");
        }
        $res    = Sys::app()->redis('cache')->get(self::getCacheKey() . "_{$user->id}_{$item_code}");
        $amount = 0;
        if ($res === false || $force_flush)
        {// 有没有key 和   val 是空值 是两码事
            $dao = RolePerkDao::model()->findOneByWhere(['role_id' => $user->id, 'perk_item_code' => $item_code, 'is_ok' => Opt::isOk], false);
            if (!empty($dao))
            {
                $amount = intval($dao->used_times);
            }
        }
        else
        {
            $amount = intval($res);
        }
        Sys::app()->redis('cache')->set(self::getCacheKey() . "_{$user->id}_{$item_code}", $amount);
        return $amount;

    }
}
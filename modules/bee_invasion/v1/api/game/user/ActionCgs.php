<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\user\UserCg;


class ActionCgs extends GameBaseAction
{


    /**
     * @return array
     * @throws \Exception
     */
    public function run()
    {


        $user_cg_daos = UserCgDao::model()->findAllByWhere(['user_id' => $this->user->id]);
        $list         = [];
        if (count($user_cg_daos))
        {
            $amount_map = [];
            foreach ($user_cg_daos as $user_cg_dao)
            {
                $amount_map[$user_cg_dao->item_code] = $user_cg_dao->item_amount > 0 ? $user_cg_dao->item_amount : 0;
            }

            $cgs = (new ConsumableGoods())->getItemInfos();

            foreach ($cgs as $cg)
            {
                $tmp_ar           = $cg->getOpenInfo();
                $tmp_ar['amount'] = isset($amount_map[$cg->item_code]) ? $amount_map[$cg->item_code] : 0;
                $list[]           = $tmp_ar;
            }
        }


        return ['list' => $list];

    }
}
<?php

namespace modules\bee_invasion\v1\api\game\rank;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\user\UserLoginTokenDao;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserObjectHis;
use modules\bee_invasion\v1\model\user\UserRankAwardHis;


class ActionAwardHis extends GameBaseAction
{
    public function run()
    {
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $page_index = $this->inputDataBox->getIntNotNull('page_index');
        $page_size  = $page_size ? $page_size : 20;
        $conditions = ['user_id' => $this->user->id];


        $dao = UserRankAwardHis::model()->addSort('id', 'desc');
        if ($page_size && $page_index)
        {
            $dao->setPage($page_index, $page_size)->setOptCountTotalStatus(true);

        }
        $award_his_daos = $dao->findAllByWhere($conditions, false);

        $currency_his_ids = [];
        $object_his_ids   = [];
        $cg_his_ids       = [];

        foreach ($award_his_daos as $i => $award_his_dao)
        {
            if ($award_his_dao->item_class === 'currency')
            {
                $currency_his_ids[$award_his_dao->his_id] = $i;
            }
            else if ($award_his_dao->item_class === 'object')
            {
                $object_his_ids[$award_his_dao->his_id] = $i;
            }
            else if ($award_his_dao->item_class === 'cg')
            {
                $cg_his_ids[$award_his_dao->his_id] = $i;
            }
        }
        if (count($object_his_ids))
        {
            $his_daos = UserObjectHis::model()->findAllByWhere(['id' => array_keys($object_his_ids)]);
            foreach ($his_daos as $his_dao)
            {
                $award_his_daos[$object_his_ids[$his_dao->id]]->setHisDao($his_dao);
            }
        }
        if (0)
        {
            if (count($currency_his_ids))
            {
                $his_daos = UserCurrencyHis::model()->findAllByWhere(['id' => array_keys($currency_his_ids)]);
                foreach ($his_daos as $his_dao)
                {
                    $award_his_daos[$currency_his_ids[$his_dao->id]]->setHisDao($his_dao);
                }
            }

            if (count($object_his_ids))
            {
                $his_daos = UserObjectHis::model()->findAllByWhere(['id' => array_keys($object_his_ids)]);
                foreach ($his_daos as $his_dao)
                {
                    $award_his_daos[$object_his_ids[$his_dao->id]]->setHisDao($his_dao);
                }
            }
            if (count($cg_his_ids))
            {
                $his_daos = UserCgHis::model()->findAllByWhere(['id' => array_keys($cg_his_ids)]);
                foreach ($his_daos as $his_dao)
                {
                    $award_his_daos[$cg_his_ids[$his_dao->id]]->setHisDao($his_dao);
                }
            }
        }


        $page_info         = $dao->getPageInfo();
        $page_info['list'] = array_map(function (UserRankAwardHis $model) { return $model->getOpenInfo(); }, $award_his_daos);
        return $page_info;
    }
}
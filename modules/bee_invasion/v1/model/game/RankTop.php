<?php

namespace modules\bee_invasion\v1\model\game;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\ChannelDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\game\rank\RankTopDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserRankAwardHis;

class RankTop extends RankTopDao
{
    use TItem;

    const cacheConfigDailyKey = 'RankTopDaily';


    private static $date_infos = [];


    /**
     * 获取所有通货code
     * @param string $channel_code
     * @param int $date_index
     * @param bool $force_flush
     * @return array|mixed
     * @throws \Exception
     */
    public function getList($channel_code, $date_index, $force_flush = false)
    {
        $res = '';
        if ($force_flush === false)
        {
            if (isset(self::$date_infos[$channel_code . '_' . $date_index]) > 0)
            {
                return self::$date_infos[$channel_code . '_' . $date_index];
            }
            $res = $this->getCache(self::cacheConfigDailyKey, ['channel_code' => $channel_code, 'date_index' => $date_index], false);
        }
        if (empty($res) || $force_flush)
        {
            $models   = RankTopDao::model()->setLimit(0, 200)->addSort('rank_sn', 'asc')->findAllByWhere(['channel_code' => $channel_code, 'date_index' => $date_index, 'is_ok' => Opt::isOk]);
            $infos    = [];
            $user_ids = [];
            foreach ($models as $i => $model)
            {
                $infos[$model->user_id] = $model->getOpenInfo();
                $user_ids[]             = $model->user_id;
            }
            if ($user_ids)
            {
                $user_daos = User::model()->setLimit(0, count($user_ids))->findAllByWhere(['id' => $user_ids]);
                foreach ($user_daos as $user_dao)
                {
                    $infos[$user_dao->id]['user']   = $user_dao->getOpenInfo();
                    $infos[$user_dao->id]['awards'] = [];
                }

                $reward_his_infos = UserRankAwardHis::model()->setLimit(0, count($user_ids) * 5)->addSort('user_id', 'asc')->addSort('award_sn', 'asc')->findAllByWhere(['channel_code' => $channel_code, 'date_index' => $date_index]);

                $cg_codes       = [];
                $currency_codes = [];
                $object_codes   = [];
                /** @var  $item_map ConsumableGoods[]|MObject[]|Currency[] */
                $item_map = [];

                foreach ($reward_his_infos as $reward_his_info)
                {
                    if ($reward_his_info->item_class === 'currency')
                    {
                        $currency_codes[] = $reward_his_info->item_code;
                    }
                    else if ($reward_his_info->item_class === 'cg')
                    {
                        $cg_codes[] = $reward_his_info->item_code;

                    }
                    else if ($reward_his_info->item_class === 'object')
                    {
                        $object_codes[] = $reward_his_info->item_code;
                    }
                }

                if (count($cg_codes))
                {
                    $tmp_models = ConsumableGoods::model()->findAllByWhere(['item_code' => $cg_codes]);
                    foreach ($tmp_models as $tmp_model)
                    {
                        $item_map['cg/' . $tmp_model->item_code] = $tmp_model;
                    }
                }
                if (count($currency_codes))
                {
                    $tmp_models = Currency::model()->findAllByWhere(['item_code' => $currency_codes]);
                    foreach ($tmp_models as $tmp_model)
                    {
                        $item_map['currency/' . $tmp_model->item_code] = $tmp_model;
                    }
                }
                if (count($object_codes))
                {
                    $tmp_models = MObject::model()->findAllByWhere(['item_code' => $object_codes]);
                    foreach ($tmp_models as $tmp_model)
                    {
                        $item_map['object/' . $tmp_model->item_code] = $tmp_model;
                    }
                }

                foreach ($reward_his_infos as $reward_his_info)
                {
                    $infos[$reward_his_info->user_id]['awards'][] = [
                        'item'        => $item_map["{$reward_his_info->item_class}/{$reward_his_info->item_code}"]->getOpenInfo(),
                        'item_amount' => $reward_his_info->item_amount,
                        'detail'      => $reward_his_info->award_detail,
                    ];
                }

            }

            self::$date_infos[$channel_code . '_' . $date_index] = array_values($infos);
            $this->setCache(self::cacheConfigDailyKey, ['channel_code' => $channel_code, 'date_index' => $date_index], $infos);
        }
        else
        {
            self::$date_infos[$channel_code . '_' . $date_index] = json_decode($res, true);
        }
        return self::$date_infos[$channel_code . '_' . $date_index];


    }


    /**
     * @param $channel_code
     * @param $ymd
     * @param $user_id
     * @return array
     * @throws \Exception
     */
    public function getRankInfo($channel_code, $ymd, $user_id)
    {
        $key         = "rank{$ymd}_{$channel_code}";
        $user_score  = false;
        $pre_user_id = false;
        $pre_score   = false;
        $user_rank   = Sys::app()->redis('cache')->zRevRank($key, $user_id);

        if ($user_rank)
        {
            $pre_info = Sys::app()->redis('cache')->zRevRange($key, $user_rank - 1, $user_rank, true);
            if (count($pre_info))
            {
                $user_score = $pre_info[$user_id];
                unset($pre_info[$user_id]);
                foreach ($pre_info as $k => $v)
                {
                    $pre_user_id = $k;
                    $pre_score   = $v;
                }
            }
        }
        else
        {
            if ($user_rank === 0)
            {
                $pre_info   = Sys::app()->redis('cache')->zRevRange($key, 0, 0, true);
                $user_score = $pre_info[$user_id];
            }
        }
        return [
            'user_score'  => $user_score,
            'user_rank'   => $user_rank,
            'pre_user_id' => $pre_user_id,
            'pre_score'   => $pre_score,
        ];


    }


}
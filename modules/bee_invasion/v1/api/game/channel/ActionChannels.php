<?php

namespace modules\bee_invasion\v1\api\game\channel;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserLoginTokenDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\RankTop;

ini_set('max_execution_time', 10);

class ActionChannels extends ActionBase
{
    public function run()
    {
        $ext_str = $this->inputDataBox->tryGetString('ext');
        if ($ext_str)
        {
            $ext     = explode(',', $ext_str);
            $user_id = false;
            $token   = $this->inputDataBox->getStringNotNull('user_token');
            //$channel_code = $this->inputDataBox->getStringNotNull('channel_code');
            if ($token)
            {
                $user_token = UserLoginTokenDao::model()->findOneByWhere(['user_token' => $token], false);
                if (empty($user_token))
                {
                    throw  new AdvError(AdvError::request_param_verify_fail);
                }
                else
                {
                    $user_id = $user_token->user_id;
                }

            }

            $channel_models = (new Channel())->getItemInfos();
            $res            = [];
            if (in_array('user_now_rank', $ext, true))
            {
                $ymd = date('Ymd', time());


                $cg_codes       = [];
                $currency_codes = [];
                $object_codes   = [];

                $code2indexs = [];

                foreach ($channel_models as $channel_code => $channel)
                {
                    $info = $channel->getOpenInfo();

                    foreach ($info['opts']['rank'] as $rank_i => $rank_info)
                    {
                        foreach ($rank_info['awards'] as $award_i => $award_info)
                        {
                            $item_flag = $award_info['item_flag'];
                            list($item_class, $item_code) = explode('/', $item_flag);
                            if (!isset($code2indexs[$item_flag]))
                            {
                                $code2indexs[$item_flag] = [];
                            }
                            $code2indexs[$item_flag][] = [count($res), $rank_i, $award_i];
                            if ($item_class === 'currency')
                            {
                                $currency_codes[] = $item_code;
                            }
                            else if ($item_class === 'object')
                            {
                                $object_codes[] = $item_code;
                            }
                            else if ($item_class === 'cg')
                            {
                                $cg_codes[] = $item_code;
                            }
                        }
                    }

                    Sys::app()->addLog([$code2indexs, $cg_codes, $currency_codes, $object_codes], '$code2indexs');


                    $info['user_now_rank'] = RankTop::model()->getRankInfo($channel->item_code, $ymd, $user_id);

                    if ($info['user_now_rank']['user_rank'] !== false)
                    {
                        if ($info['user_now_rank']['user_score'] !== false)
                        {
                            if ($info['user_now_rank']['pre_score'] === false)
                            {
                                $info['user_now_rank']['pre_score'] = $info['user_now_rank']['user_score'] + rand(50, 100);
                            }
                            $info['user_now_rank']['trailing'] = $info['user_now_rank']['pre_score'] - $info['user_now_rank']['user_score'];
                        }
                        $info['user_now_rank']['user_rank'] = $info['user_now_rank']['user_rank'] + 1 + 5;
                    }


                    $res[] = $info;
                }


                /** @var  $item_map ConsumableGoods[]|MObject[]|Currency[] */
                $item_map = [];

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
                foreach ($item_map as $class_flag => $item)
                {
                    foreach ($code2indexs[$class_flag] as $indexs)
                    {
                        list($res_i, $rank_i, $award_i) = $indexs;
                        $res[$res_i]['opts']['rank'][$rank_i]['awards'][$award_i]['item'] = $item->getOpenInfo();
                        Sys::app()->addLog([$indexs, $res[$res_i]['opts']['rank'][$rank_i]['awards'][$award_i]], 'item');
                    }
                }


            }
            return ['list' => $res];

        }
        else
        {
            return ['list' => array_map(function ($model) { return $model->getOpenInfo(); }, array_values((new Channel())->getItemInfos()))];
        }

    }
}
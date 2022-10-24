<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\bee_invasion;

use models\common\CmdBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\param\DataBox;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\Printer;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\economy\Order;
use modules\bee_invasion\v1\model\economy\plat\Partner;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\task\AsyncTask;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserObjectHis;
use modules\bee_invasion\v1\model\user\UserRankAwardHis;

class CmdAsyncTask extends CmdBase
{


    public function handleAdReward4Inviter()
    {
        $printer  = new Printer();
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 广告奖励  start\n";

        $redis  = ApiCache::model()->getRedis();
        $config = Config::model()->getItemByCode('adWatchReward4Inviter')->setting;
        if ($config['status'] !== true)
        {
            $printer->tabEcho('返现设置为关闭，不予返现，停止');
            return false;
        }
        $goods_code = 'gold_ingot';

        $cycle_i = 0;
        while (1)
        {
            if ($this->getCountdownSeconds() < 100)
            {
                if ((time() % 60) > 45)
                {
                    $deadLine = date('Y-m-d H:i:s', $this->deadLineTs);
                    $now_date = date('Y-m-d H:i:s', time());
                    echo "\nnow:{$now_date}  deadline:{$deadLine}  :自动退出\n";
                    break;
                }
            };
            $cycle_i++;
            $now_ts = time();
            $date   = date('Y-m-d H:i:s', $now_ts);
            $printer->newTabEcho('cycle', "{$date}:{$cycle_i}");
            $tasks = AsyncTask::model()->findAllByWhere(['op' => AsyncTask::opAdReward4Inviter, 'is_ok' => Opt::YES, 'is_complete' => Opt::NOT]);
            if (empty($tasks))
            {
                usleep(1000000);
                continue;
            }

            foreach ($tasks as $task_i => $task)
            {
                $info = $task->getOpenInfo();

                if ($config['lev1']['status'] !== true)
                {
                    $printer->tabEcho('1级邀请人关闭，不予返现，停止');
                    return false;
                }
                if ($config['lev2']['status'] !== true)
                {
                    $printer->tabEcho('1级邀请人关闭，不予返现，停止');
                    return false;
                }


                $task_unique = "a_task_{$task->id}";
                $printer->newTabEcho('task_item', var_export($task->getOuterDataArray(), true));
                if ($redis->setnx($task_unique, 1))
                {
                    $redis->expire($task_unique, 10);
                }
                else
                {
                    $printer->tabEcho('ERROR:被锁，停止');
                    continue;
                }

                $info       = $task->getOpenInfo();
                $op_param   = $info['op_param'];
                $op_databox = new DataBox($op_param);
                $ad_note    = $op_databox->tryGetString('ad_note');
                $user_id    = $op_databox->tryGetInt('user_id');
                // $item_amount = $op_databox->tryGetInt('gold_ingot_amount');

                if ($user_id === 0 || $ad_note === '')
                {
                    $task->is_complete = AsyncTask::isCompleteError;
                    $task->update();
                    $printer->tabEcho('ERROR:信息异常，不予返现，停止');
                    continue;
                }

                $printer->newTabEcho('task_item_reward', '#');
                $user                  = User::model()->findByPk($user_id);
                $lev1_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $user->id], false);
                if ($lev1_inviter_relation && $lev1_inviter_relation->inviter_id && $lev1_inviter_relation->is_ok === Opt::isOk)
                {
                    $lev1_inviter_user = User::model()->findByPk($lev1_inviter_relation->inviter_id, false);
                    if ($lev1_inviter_user)
                    {
                        $printer->newTabEcho('task_item_reward_try_lev1', '#');

                        if ($config['lev1']['status'] === true)
                        {
                            foreach ($config['lev1']['awards'] as $award_sn => $award_info)
                            {
                                $printer->newTabEcho('task_item_reward_try_lev1_award', "奖励:{$award_sn} {$award_info['item_flag']} " . json_encode($award_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                                list($item_class, $item_code) = explode('/', $award_info['item_flag']);
                                $item_amount = 1;
                                ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev1_inviter_user->id], time());
                                $printer->tabEcho("\n一级邀请人 {$lev1_inviter_relation->inviter_id} : {$lev1_inviter_user->nickname}\n");
                                $lev1_goods_account = UserCurrency::model()->setUser($lev1_inviter_user)->getAccount($item_code);
                                $printer->tabEcho("\n一级邀请人账号 {$lev1_goods_account->id}:{$lev1_goods_account->user_id} \n");
                                $lev1_goods_his = (new UserCurrencyHis())->setUserAccountModel($lev1_goods_account)->setOperationStep(1);
                                $lev1_amount    = intval($item_amount * $award_info['number'][0] * pow(10, (8 - $award_info['number'][1])));
                                $lev1_goods_account->verifyKeyProperties();
                                $lev1_goods_his->setOperationStep(1);
                                $printer->tabEcho("\n一级邀请人 {$lev1_goods_his->user_id} amount:{$lev1_amount} note:{$ad_note}\n");
                                $lev1_goods_record_res = $lev1_goods_his->tryRecord(UserCurrencyHis::srcAdLev1Inviter, $ad_note, $lev1_amount);
                                if ($lev1_goods_record_res === false)
                                {
                                    $printer->tabEcho("\n记录 bill 一级邀请人 {$goods_code}*{$lev1_amount} 失败\n");
                                }
                                else
                                {
                                    $printer->tabEcho("\n记录 bill 一级邀请人 {$goods_code}*{$lev1_amount} 成功\n");
                                }
                                $printer->endTabEcho('task_item_reward_try_lev1_award', "奖励:{$award_sn}");
                            }

                        }
                        else
                        {
                            $printer->tabEcho('1级邀请人关闭，不予返现，停止');
                        }
                        $printer->endTabEcho('task_item_reward_try_lev1', '#');


                        $printer->newTabEcho('task_item_reward_try_lev2', '#');

                        if ($config['lev2']['status'] === true)
                        {
                            $lev2_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $lev1_inviter_user->id], false);
                            if ($lev2_inviter_relation && $lev2_inviter_relation->inviter_id && $lev2_inviter_relation->is_ok === Opt::isOk)
                            {
                                $lev2_inviter_user = User::model()->findByPk($lev2_inviter_relation->inviter_id, false);
                                if ($lev2_inviter_user)
                                {
                                    foreach ($config['lev2']['awards'] as $award_sn => $award_info)
                                    {
                                        $printer->newTabEcho('task_item_reward_try_lev2_award', "奖励:{$award_sn} {$award_info['item_flag']}");
                                        list($item_class, $item_code) = explode('/', $award_info['item_flag']);
                                        $item_amount = 1;
                                        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev2_inviter_user->id], time());
                                        $printer->tabEcho("\n一级邀请人 {$lev2_inviter_relation->inviter_id} : {$lev2_inviter_user->nickname}\n");
                                        $lev2_goods_account = UserCurrency::model()->setUser($lev2_inviter_user)->getAccount($item_code);
                                        $lev2_goods_his     = (new UserCurrencyHis())->setUserAccountModel($lev2_goods_account)->setOperationStep(1);
                                        $lev2_amount        = intval($item_amount * $award_info['number'][0] * pow(10, (8 - $award_info['number'][1])));
                                        $lev2_goods_account->verifyKeyProperties();
                                        $lev2_goods_his->setOperationStep(1);
                                        $lev2_goods_record_res = $lev2_goods_his->tryRecord(UserCurrencyHis::srcAdLev2Inviter, $ad_note, $lev2_amount);
                                        if ($lev2_goods_record_res === false)
                                        {
                                            $printer->tabEcho("\n记录 bill 二级邀请人 {$goods_code}*{$lev2_amount} 失败\n");
                                        }
                                        else
                                        {
                                            $printer->tabEcho("\n记录 bill 二级邀请人 {$goods_code}*{$lev2_amount} 成功\n");
                                        }
                                        $printer->endTabEcho('task_item_reward_try_lev2_award', "奖励:{$award_sn} {$award_info['item_flag']}");
                                    }
                                }
                                else
                                {
                                    $printer->tabEcho("\n记录 bill 二级邀请人 信息有误 \n");
                                }
                            }
                            else
                            {
                                $printer->tabEcho("\n记录 bill 没有二级邀请人\n");
                            }
                        }
                        else
                        {
                            $printer->tabEcho('2级邀请人关闭，不予返现，停止');
                        }
                        $printer->endTabEcho('task_item_reward_try_lev2', '#');

                    }
                    else
                    {
                        $printer->tabEcho("\n记录 bill 一级邀请人 信息有误 \n");
                    }
                }
                else
                {
                    $printer->tabEcho("\n记录 bill 没有一级邀请人\n");
                }
                $task->is_complete = Opt::YES;
                $task->update();
                $printer->endTabEcho('task_item_reward', '#');


                // usleep(1000000);


            }
            usleep(1000000);
            $printer->endTabEcho('cycle', "{$date}:{$cycle_i}");
        }

    }

    public function handleShoppingReward4Inviter()
    {
        $printer  = new Printer();
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 消费奖励  start\n";
        $redis = ApiCache::model()->getRedis();


        $lev1_config = Config::model()->getItemByCode('rate_4_gold_ingot_lev1_inviter', false);
        $lev2_config = Config::model()->getItemByCode('rate_4_gold_ingot_lev2_inviter', false);
        if (!($lev1_config->is_ok === Opt::YES || $lev2_config->is_ok === Opt::YES))
        {
            $printer->tabEcho('两级邀请人都设置为关闭，不予返现，停止');
            return false;
        }
        $lev1_rate = $lev1_config->setting['rate'];
        $lev2_rate = $lev2_config->setting['rate'];

        $goods_code = 'gold_ingot';
        $cycle_i    = 0;
        while (1)
        {
            if ($this->getCountdownSeconds() < 100)
            {
                if ((time() % 60) > 45)
                {
                    $deadLine = date('Y-m-d H:i:s', $this->deadLineTs);
                    $now_date = date('Y-m-d H:i:s', time());
                    echo "\nnow:{$now_date}  deadline:{$deadLine}  :自动退出\n";
                    break;
                }
            };
            $cycle_i++;
            $now_ts = time();
            $date   = date('Y-m-d H:i:s', $now_ts);
            $printer->newTabEcho('cycle', "{$date}:{$cycle_i}");
            $tasks = AsyncTask::model()->findAllByWhere(['op' => AsyncTask::opShoppingReward4Inviter, 'is_ok' => Opt::YES, 'is_complete' => Opt::NOT]);
            if (empty($tasks))
            {
                usleep(1000000);
            }

            foreach ($tasks as $task_i => $task)
            {
                $task_unique = "a_task_{$task->id}";
                $printer->newTabEcho('task_item', var_export($task->getOuterDataArray(), true));
                if ($redis->setnx($task_unique, 1))
                {
                    $redis->expire($task_unique, 10);
                }
                else
                {
                    $printer->tabEcho('ERROR:被锁，停止');
                    continue;
                }

                $info       = $task->getOpenInfo();
                $op_param   = $info['op_param'];
                $op_databox = new DataBox($op_param);
                $order_id   = $op_databox->tryGetInt('order_id');
                // $item_amount = $op_databox->tryGetInt('gold_ingot_amount');

                $item_amount = $op_databox->tryGetInt('pay_amount');
                if ($order_id === 0 || $item_amount === 0)
                {
                    $task->is_complete = AsyncTask::isCompleteError;
                    $task->update();
                    $printer->tabEcho('ERROR:信息异常，不予返现，停止');
                    continue;
                }
                $order = Order::model()->findByPk($op_param['order_id']);

                if ($lev1_config->is_ok === Opt::NOT)
                {
                    $printer->tabEcho('1级邀请人关闭，不予返现，停止');
                    continue;
                }
                $printer->newTabEcho('task_item_reward', '#');
                $user                  = User::model()->findByPk($order->user_id);
                $lev1_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $user->id], false);
                if ($lev1_inviter_relation && $lev1_inviter_relation->inviter_id && $lev1_inviter_relation->is_ok === Opt::isOk)
                {
                    $lev1_inviter_user = User::model()->findByPk($lev1_inviter_relation->inviter_id, false);
                    if ($lev1_inviter_user)
                    {
                        $printer->newTabEcho('task_item_reward_try_lev1', '#');

                        if ($lev1_config->is_ok === Opt::YES)
                        {
                            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev1_inviter_user->id], time());
                            $printer->tabEcho("\n一级邀请人 {$lev1_inviter_relation->inviter_id} : {$lev1_inviter_user->nickname}\n");
                            $lev1_goods_account = UserCurrency::model()->setUser($lev1_inviter_user)->getAccount($goods_code);
                            $printer->tabEcho("\n一级邀请人账号 {$lev1_goods_account->id}:{$lev1_goods_account->user_id} \n");
                            $lev1_goods_his = (new UserCurrencyHis())->setUserAccountModel($lev1_goods_account)->setOperationStep(1);
                            $lev1_amount    = intval($item_amount * $lev1_rate[0] / pow(10, $lev1_rate[1]));
                            $lev1_goods_account->verifyKeyProperties();
                            $lev1_goods_his->setOperationStep(1);
                            $printer->tabEcho("\n一级邀请人 {$lev1_goods_his->user_id} \n");
                            $lev1_goods_record_res = $lev1_goods_his->tryRecord(UserCurrencyHis::srcLev1Inviter, $order->id, $lev1_amount);
                            if ($lev1_goods_record_res === false)
                            {
                                $printer->tabEcho("\n记录 bill 一级邀请人 {$goods_code}*{$lev1_amount} 失败\n");
                                $records[] = 0;
                            }
                            else
                            {
                                $printer->tabEcho("\n记录 bill 一级邀请人 {$goods_code}*{$lev1_amount} 成功\n");
                                $records[] = 1;
                            }
                        }
                        else
                        {
                            $printer->tabEcho('1级邀请人关闭，不予返现，停止');
                        }
                        $printer->endTabEcho('task_item_reward_try_lev1', '#');


                        $printer->newTabEcho('task_item_reward_try_lev2', '#');

                        if ($lev2_config->is_ok === Opt::YES)
                        {
                            $lev2_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $lev1_inviter_user->id], false);
                            if ($lev2_inviter_relation && $lev2_inviter_relation->inviter_id && $lev2_inviter_relation->is_ok === Opt::isOk)
                            {
                                $lev2_inviter_user = User::model()->findByPk($lev2_inviter_relation->inviter_id, false);
                                if ($lev2_inviter_user)
                                {
                                    ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev2_inviter_user->id], time());
                                    $printer->tabEcho("\n一级邀请人 {$lev2_inviter_relation->inviter_id} : {$lev2_inviter_user->nickname}\n");
                                    $lev2_goods_account = UserCurrency::model()->setUser($lev2_inviter_user)->getAccount($goods_code);
                                    $lev2_goods_his     = (new UserCurrencyHis())->setUserAccountModel($lev2_goods_account)->setOperationStep(1);
                                    $lev2_amount        = intval($item_amount * $lev2_rate[0] / pow(10, $lev2_rate[1]));
                                    $lev2_goods_account->verifyKeyProperties();
                                    $lev2_goods_his->setOperationStep(1);
                                    $lev2_goods_record_res = $lev2_goods_his->tryRecord(UserCurrencyHis::srcLev2Inviter, $order->id, $lev2_amount);
                                    if ($lev2_goods_record_res === false)
                                    {
                                        $printer->tabEcho("\n记录 bill 二级邀请人 {$goods_code}*{$lev2_amount} 失败\n");
                                        $records[] = 0;
                                    }
                                    else
                                    {
                                        $printer->tabEcho("\n记录 bill 二级邀请人 {$goods_code}*{$lev2_amount} 成功\n");
                                        $records[] = 1;
                                    }
                                }
                                else
                                {
                                    $printer->tabEcho("\n记录 bill 二级邀请人 信息有误 \n");
                                }
                            }
                            else
                            {
                                $printer->tabEcho("\n记录 bill 没有二级邀请人\n");
                            }
                        }
                        else
                        {
                            $printer->tabEcho('2级邀请人关闭，不予返现，停止');
                        }
                        $printer->endTabEcho('task_item_reward_try_lev2', '#');

                    }
                    else
                    {
                        $printer->tabEcho("\n记录 bill 一级邀请人 信息有误 \n");
                    }
                }
                else
                {
                    $printer->tabEcho("\n记录 bill 没有一级邀请人\n");
                }
                $task->is_complete = Opt::YES;
                $task->update();
                $printer->endTabEcho('task_item_reward', '#');
            }
            usleep(1000000);

        }

    }


}
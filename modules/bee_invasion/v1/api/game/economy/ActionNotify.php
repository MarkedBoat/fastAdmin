<?php

namespace modules\bee_invasion\v1\api\game\economy;

use models\Api;
use models\common\ActionBase;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\economy\OrderBillDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\Order;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionNotify extends ActionBase
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'JSON_STRING';

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        $order_id     = $this->inputDataBox->getStringNotNull('partner_order_id');
        $partner_code = Sys::app()->params['bee_invasion_pay']['partner_code'];
        $pri_key      = file_get_contents(Sys::app()->params['bee_invasion_pay']['private_key_file']);

        $now_ts     = time();
        $query_data = ['order_id' => $order_id];
        $json       = json_encode($query_data);
        $sign       = RSA::sign("{$json}{$partner_code}{$now_ts}", $pri_key);
        $sign       = urlencode($sign);
        $url        = Sys::app()->params['pay']['pay_page_domain'] . "/bee_invasion/v1/game/economy/plat/queryOrder?partner_code={$partner_code}&timestamp={$now_ts}&sign={$sign}";

        echo "\n{$url}\n{$json}\n";
        list($http_code, $response_text) = (new Curl())->post2($url, $json, false, 5000, 5000, Curl::application_json);
        var_dump($http_code, $response_text);
        $order_info = json_decode($response_text, true);
        if (isset($order_info['data']['partner_order_id']) && isset($order_info['data']['is_payed']) && $order_info['data']['partner_order_id'] === $order_id && $order_info['data']['is_payed'] === 'yes')
        {
            $order = Order::model()->findByPk($order_id);
            if ($order->is_payed !== Opt::isOk)
            {
                $order->is_payed   = Opt::isOk;
                $order->payed_time = $order_info['data']['payed_time'];
                $order->update(false);
                echo "\n记录支付完成\n";
            }

            if ($order->is_complete !== Opt::isOk)
            {
                $order_bills = OrderBillDao::model()->findAllByWhere(['order_id' => $order->id]);
                $user        = User::model()->findByPk($order->user_id);
                $records     = [];
                foreach ($order_bills as $order_bill)
                {
                    if (in_array('currency', $order_bill->goods_class, true))
                    {
                        $goods_account = UserCurrency::model()->setUser($user)->getAccount($order_bill->goods_code);
                        $goods_his     = (new UserCurrencyHis())->setUserAccountModel($goods_account)->setOperationStep(1);
                        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $user->id], time());
                        $goods_account->verifyKeyProperties();
                        $goods_his->setOperationStep(1);
                        $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcOrderGoods, $order->id, $order_bill->goods_amount);
                        if ($goods_record_res === false)
                        {
                            echo "\n记录 bill {$order_bill->goods_code}*{$order_bill->goods_amount} 失败\n";
                            $records[] = 0;
                        }
                        else
                        {
                            echo "\n记录 bill {$order_bill->goods_code}*{$order_bill->goods_amount} 成功\n";
                            $records[] = 1;
                        }

                        if (0 && $order_bill->goods_code === 'gold_ingot')
                        {
                            echo "\n记录 bill 是金元宝 需要给与邀请人奖励\n";

                            $lev1_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $user->id], false);
                            if ($lev1_inviter_relation && $lev1_inviter_relation->inviter_id && $lev1_inviter_relation->is_ok === Opt::isOk)
                            {
                                $lev1_inviter_user = User::model()->findByPk($lev1_inviter_relation->inviter_id, false);
                                if ($lev1_inviter_user)
                                {
                                    ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev1_inviter_user->id], time());
                                    echo "\n一级邀请人 {$lev1_inviter_relation->inviter_id} : {$lev1_inviter_user->nickname}\n";
                                    $lev1_rate          = Config::model()->getItemByCode('rate_4_gold_ingot_lev1_inviter')->setting['rate'];
                                    $lev1_goods_account = UserCurrency::model()->setUser($lev1_inviter_user)->getAccount($order_bill->goods_code);
                                    echo "\n一级邀请人账号 {$lev1_goods_account->id}:{$lev1_goods_account->user_id} \n";
                                    $lev1_goods_his = (new UserCurrencyHis())->setUserAccountModel($lev1_goods_account)->setOperationStep(1);
                                    $lev1_amount    = intval($order_bill->goods_amount * $lev1_rate / 10000);
                                    $lev1_goods_account->verifyKeyProperties();
                                    $lev1_goods_his->setOperationStep(1);
                                    echo "\n一级邀请人 {$lev1_goods_his->user_id} \n";
                                    $lev1_goods_record_res = $lev1_goods_his->tryRecord(UserCurrencyHis::srcLev1Inviter, $order->id, $lev1_amount);
                                    if ($lev1_goods_record_res === false)
                                    {
                                        echo "\n记录 bill 一级邀请人 {$order_bill->goods_code}*{$lev1_amount} 失败\n";
                                        $records[] = 0;
                                    }
                                    else
                                    {
                                        echo "\n记录 bill 一级邀请人 {$order_bill->goods_code}*{$lev1_amount} 成功\n";
                                        $records[] = 1;
                                    }


                                    $lev2_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $lev1_inviter_user->id], false);
                                    if ($lev2_inviter_relation && $lev2_inviter_relation->inviter_id && $lev2_inviter_relation->is_ok === Opt::isOk)
                                    {
                                        $lev2_inviter_user = User::model()->findByPk($lev2_inviter_relation->inviter_id, false);
                                        if ($lev2_inviter_user)
                                        {
                                            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev2_inviter_user->id], time());
                                            echo "\n一级邀请人 {$lev2_inviter_relation->inviter_id} : {$lev2_inviter_user->nickname}\n";
                                            $lev2_rate          = Config::model()->getItemByCode('rate_4_gold_ingot_lev2_inviter')->setting['rate'];
                                            $lev2_goods_account = UserCurrency::model()->setUser($lev2_inviter_user)->getAccount($order_bill->goods_code);
                                            $lev2_goods_his     = (new UserCurrencyHis())->setUserAccountModel($lev2_goods_account)->setOperationStep(1);
                                            $lev2_amount        = intval($order_bill->goods_amount * $lev2_rate / 10000);
                                            $lev2_goods_account->verifyKeyProperties();
                                            $lev2_goods_his->setOperationStep(1);
                                            $lev2_goods_record_res = $lev2_goods_his->tryRecord(UserCurrencyHis::srcLev2Inviter, $order->id, $lev2_amount);
                                            if ($lev2_goods_record_res === false)
                                            {
                                                echo "\n记录 bill 二级邀请人 {$order_bill->goods_code}*{$lev2_amount} 失败\n";
                                                $records[] = 0;
                                            }
                                            else
                                            {
                                                echo "\n记录 bill 二级邀请人 {$order_bill->goods_code}*{$lev2_amount} 成功\n";
                                                $records[] = 1;
                                            }
                                        }
                                        else
                                        {
                                            echo "\n记录 bill 二级邀请人 信息有误 \n";
                                        }
                                    }
                                    else
                                    {
                                        echo "\n记录 bill 没有二级邀请人\n";
                                    }
                                }
                                else
                                {
                                    echo "\n记录 bill 一级邀请人 信息有误 \n";
                                }
                            }
                            else
                            {
                                echo "\n记录 bill 没有一级邀请人\n";
                            }
                        }
                        $goods_his->setOperationStep(3)->update(false, false);
                    }
                }
                if (!in_array(0, $records))
                {
                    $order->is_complete = Opt::isOk;
                    $order->update();
                    echo "\n记录交付完成\n";
                }
            }

        }
        else
        {
            echo "\n数据有问题\n";
            var_dump($order_info);
        }
        //$partner         = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code]);
        echo "\nSUCCESS\n";
        \models\Api::$hasOutput = true;
        //  return ['text' => $this->rawPostData];

    }
}
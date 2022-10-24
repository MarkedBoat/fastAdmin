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
use modules\bee_invasion\v1\dao\user\UserFakeDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\Order;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionAdd extends GameBaseAction
{
    public $requestMethods = ['POST'];

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);

        UserFakeDao::model()->findByPk($this->user->id, true);
        $item_class  = $this->inputDataBox->getStringNotNull('item_class');
        $item_code   = $this->inputDataBox->getStringNotNull('item_code');
        $item_amount = $this->inputDataBox->getIntNotNull('item_amount');
        $src         = $this->inputDataBox->getStringNotNull('src');
        $src_id      = $this->inputDataBox->getStringNotNull('src_id');


        $records = [];

        if ('currency' === $item_class)
        {
            if (!isset(UserCurrencyHis::src_map[$src]))
            {
                echo "不存在的src:{$src}";
                die;
            }
            $user_account = UserCurrency::model()->setUser($this->user)->getAccount($item_code);
            $goods_his    = (new UserCurrencyHis())->setUserAccountModel($user_account)->setOperationStep(1);
            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $this->user->id], time());
            $user_account->verifyKeyProperties();
            $goods_his->setOperationStep(1);
            $goods_record_res = $goods_his->tryRecord($src, $src_id, $item_amount);
            if ($goods_record_res === false)
            {
                echo "\n记录 bill {$item_code}*{$item_amount} 失败\n";
                $records[] = 0;
            }
            else
            {
                echo "\n记录 bill {$item_code}*{$item_amount} 成功\n";
                $records[] = 1;
            }

            if ($src === UserCurrencyHis::srcOrderGoods && $item_code === 'gold_ingot')
            {
                echo "\n记录 bill 是金元宝 需要给与邀请人奖励\n";

                $lev1_inviter_relation = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $this->user->id], false);
                if ($lev1_inviter_relation && $lev1_inviter_relation->inviter_id && $lev1_inviter_relation->is_ok === Opt::isOk)
                {
                    $lev1_inviter_user = User::model()->findByPk($lev1_inviter_relation->inviter_id, false);
                    if ($lev1_inviter_user)
                    {
                        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $lev1_inviter_user->id], time());
                        echo "\n一级邀请人 {$lev1_inviter_relation->inviter_id} : {$lev1_inviter_user->nickname}\n";
                        $lev1_rate          = Config::model()->getItemByCode('rate_4_gold_ingot_lev1_inviter')->setting['rate'];
                        $lev1_goods_account = UserCurrency::model()->setUser($lev1_inviter_user)->getAccount($item_code);
                        echo "\n一级邀请人账号 {$lev1_goods_account->id}:{$lev1_goods_account->user_id} \n";
                        $lev1_goods_his = (new UserCurrencyHis())->setUserAccountModel($lev1_goods_account)->setOperationStep(1);
                        $lev1_amount    = intval($item_amount * $lev1_rate / 10000);
                        $lev1_goods_account->verifyKeyProperties();
                        $lev1_goods_his->setOperationStep(1);
                        echo "\n一级邀请人 {$lev1_goods_his->user_id} \n";
                        $lev1_goods_record_res = $lev1_goods_his->tryRecord(UserCurrencyHis::srcLev1Inviter, $src_id, $lev1_amount);
                        if ($lev1_goods_record_res === false)
                        {
                            echo "\n记录 bill 一级邀请人 {$item_code}*{$lev1_amount} 失败\n";
                            $records[] = 0;
                        }
                        else
                        {
                            echo "\n记录 bill 一级邀请人 {$item_code}*{$lev1_amount} 成功\n";
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
                                $lev2_goods_account = UserCurrency::model()->setUser($lev2_inviter_user)->getAccount($item_code);
                                $lev2_goods_his     = (new UserCurrencyHis())->setUserAccountModel($lev2_goods_account)->setOperationStep(1);
                                $lev2_amount        = intval($item_amount * $lev2_rate / 10000);
                                $lev2_goods_account->verifyKeyProperties();
                                $lev2_goods_his->setOperationStep(1);
                                $lev2_goods_record_res = $lev2_goods_his->tryRecord(UserCurrencyHis::srcLev2Inviter, $src_id, $lev2_amount);
                                if ($lev2_goods_record_res === false)
                                {
                                    echo "\n记录 bill 二级邀请人 {$item_code}*{$lev2_amount} 失败\n";
                                    $records[] = 0;
                                }
                                else
                                {
                                    echo "\n记录 bill 二级邀请人 {$item_code}*{$lev2_amount} 成功\n";
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


        echo "\nSUCCESS\n";
        \models\Api::$hasOutput = true;

    }
}
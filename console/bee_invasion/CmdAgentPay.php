<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\bee_invasion;

use models\common\CmdBase;
use models\common\sys\Sys;
use models\ext\tool\Printer;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;

class CmdAgentPay extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function pay()
    {
        $printer  = new Printer();
        $now_date = date('Y-m-d H:i:s', time());
        $ytime = date("Y-m-d",time());
        echo "\nnow:{$now_date}代理人收益发放  start\n";
        //查出所有待打钱数据
        $list = Sys::app()->db('dev')->setText("select * from bi_user_agent_pay_record where is_ok = 1 and status = 1 and DATE_FORMAT(create_time,'%Y-%m-%d') = :time")->bindArray(['time'=>$ytime])->queryAll();
        if($list){
            foreach ($list as $k => $v)
            {
                $uid  = $v['user_id'];
                $user = User::model()->findByPk($uid, false);
                if ($user)
                {
                    ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $uid], time());
                    $goods_account = UserCurrency::model()->setUser($user)->getAccount('gold_ingot');
                    $goods_his     = (new UserCurrencyHis())->setUserAccountModel($goods_account)->setOperationStep(1);
                    $goods_account->verifyKeyProperties();
                    $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcAgent_pay, $v['id'], $v['receive_money']);
                    if ($goods_record_res === false)
                    {
                        echo "\n记录代理人 {$uid}*收益{$v['receive_money']} 失败\n";
                    }else{
                        $data['id'] = $v['id'];
                        Sys::app()->db('dev')->setText("update bi_user_agent_pay_record set status = 2 where id = :id")->bindArray($data)->execute();
                        echo "\n记录代理人 {$uid}*收益{$v['receive_money']} 成功\n";
                    }
                }
                else
                {
                    echo "\n代理人 {$uid}不存在或被封禁 失败\n";
                }
            }
            echo "\n代理人收益发放结束\n";
        }else{
            $printer->tabEcho('暂无收益 结束');
            return false;
        }
    }


}
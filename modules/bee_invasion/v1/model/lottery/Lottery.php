<?php

namespace modules\bee_invasion\v1\model\lottery;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCg;

class Lottery
{
    //获取抽奖次数
    public static function getLotteryTimes($data){
        $date = date('Y-m-d');
        $cacheName = Sys::app()->params['cache_cfg']['UserLotteryNum']['key'].$data['uid'];
        $times = Sys::app()->redis('cache')->get($cacheName);
        if(!$times){
            //判断用户是无记录还是次数已耗完
            if($times !== '0'){
                //用户此时无记录 次数取表中初始记录 //8.12改 先去剩余次数 防止缓存失效 表中无记录再取初始记录
                $res = Sys::app()->db('dev')->setText("select lottery_times as times from bi_game_lottery_record where user_id = :uid order by id desc")->bindArray(['uid'=>$data['uid']])->queryRow();
                if(!$res){
                    $res = Sys::app()->db('dev')->setText("select times from bi_game_lottery_config")->queryRow();
                }
                $times = $res['times'];
                Sys::app()->redis('cache')->set($cacheName,$times);
            }

        }
        return $times;
    }

    //抽奖次数增减
    //type 1 增加
    public static function doLotteryNum($cacheName,$num,$type=''){
        if($type){
            //增加
            $times = Sys::app()->redis('cache')->incrby($cacheName,$num);
        }else{
            $times = Sys::app()->redis('cache')->decrby($cacheName,$num);
        }
        return $times;
    }

    //获取抽奖列表
    public static function getLotteryList(){
        //获取
        $cacheName = Sys::app()->params['cache_cfg']['UserLotteryList']['key'];
        //$cacheName = 'lottery';
        $res = json_decode(Sys::app()->redis('cache')->get($cacheName),true);
        if(!$res){
            //$field = 'id,lottery_name,lottery_num,lottery_icon,lottery_detail';
            $res = Sys::app()->db('dev')->setText("select * from bi_game_lottery where is_ok=:is_ok")->bindArray(['is_ok'=>1])->queryAll();
            if(!$res){
                throw  new \Exception('转盘奖品配置错误，请联系管理员', 400);
            }
            Sys::app()->redis('cache')->set($cacheName,json_encode($res),86400);
        }
        return $res;
    }

    //获取当前时间对应广告时间段值
    public static function getAdTimes($data){
        $hour = date('H');
        $date = date('Y-m-d');
        $adCache = 'AdCache'.$data['type'];
        $result = json_decode(Sys::app()->redis('cache')->get($adCache),true);
        if(!$result){
            $result = Sys::app()->db('dev')->setText("select times,start_time,end_time,gift_num from bi_game_cd_config where type=:type")->bindArray(['type'=>$data['type']])->queryAll();
            if(!$result){
                throw  new \Exception('配置出错了，请联系管理员', 400);
            }
            Sys::app()->redis('cache')->set($adCache,json_encode($result),86400);
        }

        //判断当前时间所处时间段
        foreach($result as $key=>$val){
            if($hour>= $val['start_time'] && $hour < $val['end_time']){
                $res = $val;
            }
        }
        if(!$res){
            throw  new \Exception('广告配置出错了，请联系管理员', 400);
        }
       return $res;

    }
    //type 1通货类 对应currency表类型 2 道具类 对应cg表
    //抽奖发放奖品
    public static function sendAward($data,$type = '1'){
        $arrSql['user_id'] = $data['user_id'];
        $arrSql['item_code'] = $data['item_code'];
        $where = '(user_id,item_code,item_amount) values (:user_id,:item_code,:item_amount)';
        if($type == 1){
            $arr = Sys::app()->db('dev')->setText("select id,item_amount from bi_user_currency where user_id = :user_id and item_code = :item_code")->bindArray($arrSql)->queryRow();
            if($arr){
                $data['curr_amount'] = $arr['item_amount'];
                $res = Sys::app()->db('dev')->setText("update bi_user_currency set item_amount = item_amount+:num where id = :id")->bindArray(['num'=>$data['item_amount'],'id'=>$arr['id']])->execute();
            }else{
                $res = Sys::app()->db('dev')->setText("insert into bi_user_currency".$where)->bindArray($data)->execute();
                $data['curr_amount'] = 0;
            }
            //$res = Sys::app()->db('dev')->setText("insert into bi_user_currency".$where."ON DUPLICATE KEY UPDATE user_id = :user_id,item_code = :item_code,item_amount = item_amount+:num")->bindArray($data)->execute();

        }else{
            $arr = Sys::app()->db('dev')->setText("select id,item_amount from bi_game_user_cg where user_id = :user_id and item_code = :item_code")->bindArray($arrSql)->queryRow();
            if($arr){
                $data['curr_amount'] = $arr['item_amount'];
                $res = Sys::app()->db('dev')->setText("update bi_game_user_cg set item_amount = item_amount+:num where id = :id")->bindArray(['num'=>$data['item_amount'],'id'=>$arr['id']])->execute();
            }else{
                $res = Sys::app()->db('dev')->setText("insert into bi_game_user_cg".$where)->bindArray($data)->execute();
                $data['curr_amount'] = 0;
            }
            //$res = Sys::app()->db('dev')->setText("insert into bi_game_user_cg".$where."ON DUPLICATE KEY UPDATE user_id = :user_id,item_code = :item_code,item_amount = item_amount+:num")->bindArray($data)->execute();
        }
//        if($res){
//            self::awardRecord($data,$type);
//        }
        return $res;
    }

    //奖品记录
//    public static function awardRecord($data,$type = '1'){
//        $hiswhere = '(user_id,item_code,item_amount,curr_amount,expect_amount) values (:user_id,:item_code,:item_amount,:curr_amount,:item_amount+:curr_amount)';
//        //添加操作记录
//        if($type == 1){
//            $res = Sys::app()->db('dev')->setText("insert into bi_user_currency_his".$hiswhere)->bindArray($data)->execute();
//        }else{
//            $res = Sys::app()->db('dev')->setText("insert into bi_game_user_cg_his".$hiswhere)->bindArray($data)->execute();
//        }
//        return $res;
//
//    }

    //$data user_id item_code通货类型 item_amount数量 id 唯一id
    //type 0 元宝申请提现，1签到奖励，2抽奖 3补签扣除金币
    public static function awardRecord($data,$type = '1'){
        $user        = User::model()->findByPk($data['user_id']);
        $goods_account = UserCurrency::model()->setUser($user)->getAccount($data['item_code']);
        $goods_his     = (new UserCurrencyHis())->setUserAccountModel($goods_account)->setOperationStep(1);
        $goods_account->verifyKeyProperties();
        if($type == 1){
            $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcSignAward, $data['id'], $data['item_amount']);
        }elseif ($type == 2){
            $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcLottery, $data['id'], $data['item_amount']);
        }elseif ($type == 3){
            //补签扣除金币
            $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcCounterSign, time(), $data['item_amount']);
        }else{
            //元宝减
            $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcOrderApply, time(), $data['item_amount']);
        }
        return $goods_record_res;
    }
    //cg道具
    public static function lotteryAward($data){
        $uid = $data['user_id'];
        $user        = User::model()->findByPk($uid);
        $user_account = UserCg::model()->setUser($user)->getAccount($data['item_code']);
        $his          = UserCgHis::model()->setUserAccountModel($user_account);
        $res          = $his->tryRecord(UserCgHis::srcLotteryGoods, $data['id'], $data['item_amount']);

        return $res;
    }



}
<?php

namespace modules\bee_invasion\v1\model\sign;

use models\common\opt\Opt;
use models\common\sys\Sys;

class Sign
{
    public static function resetCycle($data){
        $update         = 'cycle_day = 0';
        $res         = Sys::app()->db('dev')->setText("update bi_user_sign set " . $update . " where user_id=:id and cycle_day > 0")->bindArray($data)->execute();
        if(!$res){
            throw  new \Exception('签到状态修改失败，请重新尝试', 400);
        }
        return $res;
    }

    public static function getSignList($data){
        //加入缓存
        $res             = Sys::app()->db('dev')->setText("select *,DATE_FORMAT(create_time,'%Y-%m-%d') as time from bi_user_sign where user_id=:user_id  and cycle_day > 0 and DATE_FORMAT(create_time,'%Y-%m-%d') <= :time order by id desc")->bindAll($data)->queryRow();
        return $res??[];
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

    //获取签到奖品配置
    public static function getSignConfig(){
        //获取
        $cacheName = Sys::app()->params['cache_cfg']['UserSignConfig']['key'];
        $res = json_decode(Sys::app()->redis('cache')->get($cacheName),true);
        if(!$res){
            $res = Sys::app()->db('dev')->setText("select day,award,award_type,0 as is_sign,0 as is_countersign from bi_user_sign_config")->queryAll();
            if(!$res){
                throw  new \Exception('签到奖品配置错误，请联系管理员', 400);
            }
            Sys::app()->redis('cache')->set($cacheName,json_encode($res),86400);
        }
        return $res;
    }




    
}
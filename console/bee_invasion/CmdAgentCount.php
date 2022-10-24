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

class CmdAgentCount extends CmdBase
{

    public static function getClassName()
    {
        return __CLASS__;
    }

    public function countMoney()
    {
        $printer  = new Printer();
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date}统计昨日消费总计  start\n";
        //查出所有代理
        $areaArr = Sys::app()->db('dev')->setText("select * from bi_user_bind_area where is_ok = 1")->queryAll();
        if($areaArr){
            //获取昨天时间
            $time = date("Y-m-d",strtotime("-1 day"));
            foreach($areaArr as $key => $val){
                //获取用户消费集合
                $data['time'] = $time;
                $data['area_code'] = $val['area_code'];
                $userList = Sys::app()->db('dev')->setText("select u.area_code,o.order_sum,o.id,o.open_id,o.user_id,o.payed_time from bi_user_profile u left join bi_order o on o.user_id = u.user_id where u.is_ok = 1 and u.area_code = :area_code and o.payment_code = 'gold_ingot' and o.is_ok = 1 and o.is_payed = 1 and DATE_FORMAT(o.payed_time,'%Y-%m-%d') = :time")->bindArray($data)->queryAll();
                if($userList){
                    //获取当前收益率
                    $rate = Sys::app()->db('dev')->setText("select JSON_EXTRACT(setting,'$.rate[0]') as setting,JSON_EXTRACT(setting,'$.rate[1]') as litt from bi_game_config where item_code = 'rate_agent_gold_ingot'")->queryRow();
                    if(!$rate){
                        $printer->tabEcho('暂无代理收益配置 结束');
                        return false;
                    }
                    $srate = $rate['setting']/pow(10,$rate['litt']);
                    $insertData['gold_rate'] = $rate['setting'].",".$rate['litt'];
                    $insertData['user_id'] = $val['user_id'];
                    //所需元宝数量 为现金*比率
                    foreach ($userList as $k => $v){
                        //数据插入审核表
                        $insertData['open_id'] = $v['open_id'];
                        $insertData['pay_user_id'] = $v['user_id'];
                        $insertData['money'] = $v['order_sum'];//用户消费金额
                        $insertData['area_code'] = $v['area_code'];
                        $insertData['receive_money'] = $v['order_sum'] * $srate;

                        Sys::app()->db('dev')->setText("insert into bi_user_agent_pay_record (user_id,area_code,pay_user_id,money,receive_money,gold_rate,open_id) values (:user_id,:area_code,:pay_user_id,:money,:receive_money,:gold_rate,:open_id) ")->bindArray($insertData)->execute();
                    }
                }
            }
            $now_date = date('Y-m-d H:i:s', time());
            echo "\nnow:{$now_date}统计昨日消费总计  end\n";
        }else{
            $printer->tabEcho('暂无代理 结束');
            return false;
        }

    }


}
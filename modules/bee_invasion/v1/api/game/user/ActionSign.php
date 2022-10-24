<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\lottery\Lottery;
use modules\bee_invasion\v1\model\sign\Sign;


class ActionSign extends GameBaseAction
{

    public function run()
    {
        $result          = ['code' => '0', 'status' => '400', 'msg' => '签到失败'];
        $data['user_id'] = $this->user->id;
        //获取上一条签到记录 获取连签数
        $res             = Sys::app()->db('dev')->setText("select *,DATE_FORMAT(create_time,'%Y-%m-%d') as time from bi_user_sign where user_id=:user_id and cycle_day > 0 order by id desc")->bindArray($data)->queryRow();
        $nowDate         = date('Y-m-d');
        $where = '(user_id,sign_count,cycle_day,award_num,award_type) values (:user_id,:sign_count,:cycle_day,:award_num,:award_type)';
        if ($res)
        {
            //获取最大的签到日期 获取周期数
            $listTime             = Sys::app()->db('dev')->setText("select DATE_FORMAT(create_time,'%Y-%m-%d') as time,cycle_day from bi_user_sign where user_id=:user_id and cycle_day > 0 order by create_time desc")->bindArray($data)->queryRow();

            //判断签到状态
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            if ($listTime['time'] == $nowDate)
            {
                throw  new \Exception('今日已签到！', 400);
            }
            elseif ($listTime['time'] == $yesterday)
            {//无断签
                //判断连签天数 30为一周期
                if($listTime['cycle_day'] == 30){
                    $data['sign_count'] = $data['cycle_day'] = 1;//重新开始周期
                    //重置周期
                    Sign::resetCycle(['id'=>$this->user->id]);
                }else{
                    $data['sign_count'] = $res['sign_count'] + 1;
                    $data['cycle_day'] = $listTime['cycle_day'] +1;
                }
            }
            else
            {//有断签 断签天数大于10天开启新一轮周期
                $data['sign_count'] = 1;
                $lostDay = (strtotime($nowDate)-strtotime($listTime['time']))/86400;//计算时间差
                if( $lostDay >= 10){
                    $data['cycle_day'] = 1;
                    //重置周期
                    Sign::resetCycle(['id'=>$this->user->id]);
                }else{
                    $data['cycle_day'] = $listTime['cycle_day']+$lostDay;
                }
            }
        }
        else
        {
            $res             = Sys::app()->db('dev')->setText("select *,DATE_FORMAT(create_time,'%Y-%m-%d') as time from bi_user_sign where user_id=:user_id and DATE_FORMAT(create_time,'%Y-%m-%d') = :time")->bindArray(['user_id'=>$this->user->id,'time'=>$nowDate])->queryRow();
            if($res){
                throw  new \Exception('今日已签到！请明日再来', 400);
            }
            $data['sign_count'] = $data['cycle_day'] = 1;
        }
        //判断奖励
        $signConfig = Sign::getSignConfig();
        $signConfig = array_column($signConfig, null, 'day');
        $data['award_num'] = $signConfig[$data['sign_count']]['award'];
        if($signConfig[$data['sign_count']]['award_type'] == 1){
            //金币
            $awardData['item_code'] = 'gold_coin';
            $data['award_type'] = 1;
            $arr['num'] = '+'.$data['award_num'].'金币';
        }else{
            //门票
            $data['award_type'] = 2;
            $awardData['item_code'] = 'vip_channel_ticket';
            $arr['num'] = '+'.$data['award_num'].'门票';
        }
        foreach ($signConfig as $key=>$val){
            if($data['sign_count'] < $key && $val['award_type'] == 2){
                $arr['info'] = '再签到'.$key-$data['sign_count'].'天，即可获得门票'.$val['award'].'张';
                break;
            }else{
                $arr['info'] = '恭喜完成本轮签到';
            }
        }
        $res = Sys::app()->db('dev')->setText("insert into bi_user_sign" . $where)->bindArray($data)->execute();
        if ($res)
        {
            //发放奖励
            $awardData['user_id'] = $data['user_id'];
            $id = Sys::app()->db('dev')->setText("bi_user_sign")->lastInsertId();
            $awardData['item_amount'] = $data['award_num'];
            $awardData['id'] = $id;
            $res = Lottery::awardRecord($awardData);
            $lostNum = $res->expect_amount;

            $result = ['code' => 'ok', 'status' => '200', 'msg' => '签到成功','data'=>['data'=>$lostNum]];
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);
        exit;
    }

}
<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\lottery\Lottery;
use modules\bee_invasion\v1\model\sign\Sign;

//补签
class ActionCounterSign extends GameBaseAction
{

    public function run()
    {
        $result          = ['code' => '0', 'status' => '400', 'msg' => '签到失败'];
        $data['user_id'] = $this->user->id;

        $type = $this->inputDataBox->tryGetString('type');
        $res             = Sys::app()->db('dev')->setText("select *,DATE_FORMAT(create_time,'%Y-%m-%d') as time from bi_user_sign where user_id=:user_id  and cycle_day > 0 order by create_time desc")->bindArray($data)->queryAll();
        if(!$res){
            throw  new \Exception('补签日期错误，请重新检测！', 400);
        }
        $countDay = count($res);//总共签到次数
        $data['time'] = $this->inputDataBox->getStringNotNull('time');
        $timeList[] = strtotime($data['time']);
        $flag = $newDay = $maxDay = $counterSign = $lastTime = 0;
        foreach ($res as $k => $v){
            $timeList[] = strtotime($v['time']);
            //判断补签日期前一天是否有数据 有正常 无不能补签
            if($v['time'] < $data['time']){
                $flag = 1;
                if(!$lastTime){
                    $lastTime = $v;//补签前一天数据
                }
            }
            //判断补签日期是否已签到
            if($v['time'] == $data['time']){
                throw  new \Exception('该日无需补签！', 400);
            }
            //获取最大周期数
            if($v['cycle_day']>$maxDay){
                $maxDay = $v['cycle_day'];
            }
            //最近一条签到数据 取id最大值
            if($v['id']>$newDay){
                $new = $v;
                $newDay = $v;
            }
            //周期内只能补签四次
            if($v['is_countersign'] == 1){
                $counterSign++;
            }

        }
        if($flag == 0){
            throw  new \Exception('补签日期不在本次周期内！', 400);
        }
        if($counterSign>=4){
            throw  new \Exception('本周期内补签次数已用完！', 400);
        }
        //判断剩余金币数
        $gold = Sys::app()->db('dev')->setText("select item_amount from bi_user_currency where user_id=:user_id and item_code = 'gold_coin'")->bindArray(['user_id'=>$this->user->id])->queryRow();
        $lostGold = $gold['item_amount'];
        //花费金币补签需扣除
        if($type == 1){
            if($gold && $gold['item_amount']-1000 >= 0){
                $lostGold = $gold['item_amount']-1000;
            }else{
                throw  new \Exception('剩余金币不足！', 400);
            }
        }
        $where = '(user_id,sign_count,cycle_day,award_num,award_type,create_time,is_countersign) values (:user_id,:sign_count,:cycle_day,:award_num,:award_type,:time,1)';
        $lostDay = (strtotime($data['time'])-strtotime($lastTime['time']))/86400;//计算时间差
        $data['cycle_day'] = $lastTime['cycle_day']+$lostDay;

        if($countDay+1 == $maxDay){
            //已补满则连签总数加一
            $data['sign_count'] = $maxDay;
        }else{
            //未补满计算最近连签数
            rsort($timeList);
            $datanum = 1;
            foreach ($timeList as $k => $v){
                if($v-86400 == $timeList[$k+1]){
                    $datanum ++;
                }else{
                    break;
                }
            }
            $data['sign_count'] = $datanum;


        }
        $data['award_num'] = $data['award_type'] = 0;
        $res = Sys::app()->db('dev')->setText("insert into bi_user_sign" . $where)->bindArray($data)->execute();
        if($res){
            if($type == 1){
                //扣除金币加记录
                $id = Sys::app()->db('dev')->setText("bi_user_sign")->lastInsertId();
                $data1['item_amount'] = 1000;
                $data1['item_code'] = 'gold_coin';
                $data1['id'] = $id;
                $data1['user_id'] = $this->user->id;
                $res = Lottery::awardRecord($data1,3);
            }
            $result          = ['code' => 'ok', 'status' => 200, 'msg' => '补签成功','data'=>['data'=>$lostGold]];
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);
        exit;
    }

}
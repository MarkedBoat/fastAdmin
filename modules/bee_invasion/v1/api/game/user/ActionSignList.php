<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\sign\Sign;


class ActionSignList extends GameBaseAction
{

    public function run()
    {
        $data['user_id'] = $this->user->id;
        //$data['date'] = date('Y-m');
        //获取本周期数据
        $signList = Sys::app()->db('dev')->setText("select *,DATE_FORMAT(create_time,'%Y-%m-%d') as time from bi_user_sign where user_id=:user_id and cycle_day > 0")->bindArray($data)->queryAll();
        //获取配置
        $signConfig = Sign::getSignConfig();
        $goldAll = $ticket = $signDay = 0;
        if($signList){
            $firstDay = strtotime($signList[0]['time']);
            foreach ($signConfig as $key=> &$val){
                $val['time'] = date('Y-m-d',$firstDay);
                $firstDay += 86400;
                foreach ($signList as $k => $v){
                    if($v['cycle_day'] == $val['day']){
                        $val['is_sign'] = 1;
                        $val['is_countersign'] = $v['is_countersign'];
                        if($v['award_type'] == 1){
                            $goldAll += $v['award_num'];
                        }else{
                            $ticket += $v['award_num'];
                        }
                    }
                }
            }
            //统计连续签到天数
            //判断有无断签
            $countDay = count($signList);//总签到天数
            //$signList = array_column($signList, null, 'cycle_day');
//            if($countDay == $signList[$countDay-1]['cycle_day']){
//                //无断签
//                $signDay = $countDay;
//            }else{
//                $signDay = $signList[$countDay-1]['sign_count'];
//            }
            $signDay = $signList[$countDay-1]['sign_count'];
        }else{
            $firstDay = strtotime(date('Y-m-d',time()));
            foreach ($signConfig as $key=> &$val){
                $val['time'] = date('Y-m-d',$firstDay);
                $firstDay += 86400;
            }
        }
        $result = ['code' => 'ok', 'status' => '200', 'msg' => '获取成功'];
        $count = array_sum(array_column($signConfig,'is_countersign'))>=4?0:1;
        $result['data']['list'] = $signConfig;
        $result['data']['goldAll'] = $goldAll;
        $result['data']['ticket'] = $ticket;
        $result['data']['signDay'] = $signDay;
        $result['data']['isCanCountersign'] = $count;
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

}
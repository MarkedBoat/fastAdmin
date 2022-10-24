<?php

namespace modules\bee_invasion\v1\api\game\lottery;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\lottery\Lottery;

class ActionDraw extends GameBaseAction
{

    //抽奖

    public function run()
    {
        //抽奖
        $result = $this->doDraw();
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }


    public function doDraw(){
        //二次校验抽奖次数
        $cache = Sys::app()->params['cache_cfg']['UserLotteryNum']['key'].$this->user->id;
        $times = Sys::app()->redis('cache')->get($cache);
        if($times<1){
            throw  new \Exception('抽奖次数已耗尽，请完成任务获取抽奖次数', 400);
        }
        $result = ['msg'=>'未能获奖','code'=>'0','status'=>400];
        //获取奖品配置
        $res = Lottery::getLotteryList();
        $arr = [];
        $max = $min = 0;
        $num = 0;
        $fix_id = 0;
        foreach ($res as $key=>$val)
        {
            $min = $max + 1;
            $max += $val['lottery_put_num'];
            $arr[$val['id']] = ['id' => $val['id'], 'min' => $min, 'max' => $max,'day_top'=>$val['day_top'],'type'=>$val['type'],'lottery_num'=>$val['lottery_num'],'imgurl'=>$val['lottery_icon']];
//            if($val['lottery_name'] == '金币'){
            $val['lottery_num'] = $val['lottery_num']/100000000;
//            }
            $arr[$val['id']]['lottery_name'] = ($val['lottery_num']>1)?$val['lottery_name'].'+'.$val['lottery_num']:$val['lottery_name'];
            //抽奖修复 给最低值奖品用于补漏
            if(!$val['day_top'] && $val['lottery_put_num']>$num){
                $num = $val['lottery_put_num'];
                $fix_id = $val['id'];
            }
        }
        //分配格式如 1-100 100-350
        //取随机数
        $drawId = mt_rand(1,$max);
        //判断随机数所在范围区间
        $limit = 0;
        $lotteryId = 0; //默认选奖品最多的
        foreach ($arr as $key => $value) {
            # code...
            if($drawId >= $value['min'] && $drawId < $value['max']){
                $lotteryId = $value['id'];
                $limit = $value['day_top'];
            }
        }
        //判断限制条件
        if($arr[$lotteryId]['day_top']){
            $create_time = date('Y-m-d');
            $limitarr = json_decode($limit);
            $count = Sys::app()->db('dev')->setText("select count(*) as num from bi_game_lottery_record where user_id=:user_id and DATE_FORMAT(create_time,'%Y-%m-%d') = :create_time and lottery_id = :lottery_id")->bindArray(['user_id'=>$this->user->id,'create_time'=>$create_time,'lottery_id'=>$lotteryId])->queryRow();
            if($count['num'] >= $limitarr->cd){
                //奖品超出限制
                $lotteryId = $fix_id; //默认选奖品最多的 且没有限制条件的
            }
        }
        if($lotteryId > 0){
            //发放奖品+记录
            $data['user_id'] = $data1['user_id'] = $this->user->id;
            //抽奖次数减一
            $date = date('Y-m-d');
            $cache = Sys::app()->params['cache_cfg']['UserLotteryNum']['key'].$data['user_id'];
            $times = Sys::app()->redis('cache')->decrby($cache,1);

            $data1['lotteryTimes'] = intval($times);
            //添加抽奖记录
            $data1['lottery_id'] = $lotteryId;
            $data1['content'] = $arr[$lotteryId]['lottery_name'];
            $where = '(user_id,lottery_id,content,lottery_times) values (:user_id,:lottery_id,:content,:lotteryTimes)';
            $res = Sys::app()->db('dev')->setText("insert into bi_game_lottery_record".$where)->bindArray($data1)->execute();
            //获取添加的id
            $id = Sys::app()->db('dev')->setText("bi_game_lottery_record")->lastInsertId();


            $type = json_decode($arr[$lotteryId]['type']);
            $data['item_amount'] = $arr[$lotteryId]['lottery_num'];
            $data['item_code'] = $type->item_code;
            $data['id'] = $id;
            //$res = Lottery::sendAward($data,$type->type);
            if($type->type == 1){
                $res = Lottery::awardRecord($data,2);
            }else{
                //增加道具
                $res = Lottery::lotteryAward($data);
            }
            if($res){

                $data1['lottery_id'] = intval($lotteryId);
                $data1['drawid'] = intval($drawId);
                $data1['imgurl'] = $arr[$lotteryId]['imgurl'];

                $result = ['code'=>'ok','status'=>200,'msg'=>"恭喜您获得".$data1['content'].",奖品已发放至您的背包请查收",'data'=>$data1];
            }else{
                throw  new \Exception('抽奖失败，请重新尝试', 400);
            }
        }
        return $result;
    }
}

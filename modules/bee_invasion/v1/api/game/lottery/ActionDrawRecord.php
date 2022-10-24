<?php

namespace modules\bee_invasion\v1\api\game\lottery;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;

class ActionDrawRecord extends GameBaseAction
{

    //抽奖记录

    public function run()
    {
        $uid = $this->user->id;
        $res = Sys::app()->db('dev')->setText("select * from bi_game_lottery_record where user_id=:user_id order by id desc limit 20")->bindArray(['user_id' =>$uid])->queryAll();
        //$result = array('ces'=>1,'as'=>['arr1'=>213]);
        //return $result;
        $resule['code'] = 'ok';
        $resule['status'] = 200;
        $resule['data']['list'] = $res??[];
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $resule['data']['op_flag'] = $op_flag;
        }
        echo json_encode($resule);exit;
    }


}

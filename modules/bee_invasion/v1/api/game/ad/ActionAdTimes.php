<?php

namespace modules\bee_invasion\v1\api\game\ad;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\lottery\Lottery;

//获取剩余抽奖次数
class ActionAdTimes extends GameBaseAction
{
    public function run()
    {
        $uid = $this->user->id;
        $data['type'] = $_REQUEST['type']??1;
        $hour = date('H');
        $date = date('Y-m-d');
        //获取广告总观看次数上限
        $res = Lottery::getAdTimes($data);

        //获取当前时段累计观看次数
        $cacheName = 'lottery_times_'.$date.'_'.$res['start_time'].'-'.$res['end_time'].$uid;
        $times = Sys::app()->redis('cache')->get($cacheName);
        if(!$times){
            $times = 0;
        }
        $result['code'] = 'ok';
        $result['status'] = 200;
        $result['data']['allTimes'] = $res['times'];
        $result['data']['useTimes'] = $times;
        echo json_encode($result);exit;

    }


}
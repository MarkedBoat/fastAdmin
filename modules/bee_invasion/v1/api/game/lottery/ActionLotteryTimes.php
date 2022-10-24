<?php

namespace modules\bee_invasion\v1\api\game\lottery;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\lottery\Lottery;
use modules\bee_invasion\v1\api\game\GameBaseAction;

//获取剩余抽奖次数
class ActionLotteryTimes extends GameBaseAction
{
    public function run()
    {
        $uid = $this->user->id;
        $data['uid'] = $uid;
        $times = Lottery::getLotteryTimes($data);
        $result['code'] = 'ok';
        $result['status'] = 200;
        $result['data'] = $times??1;
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;

    }


}
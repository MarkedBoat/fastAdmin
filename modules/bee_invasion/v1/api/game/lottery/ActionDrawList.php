<?php

namespace modules\bee_invasion\v1\api\game\lottery;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\lottery\Lottery;
use modules\bee_invasion\v1\model\game\Config;

class ActionDrawList extends GameBaseAction
{

    //抽奖

    public function run()
    {
        $data['type'] = $_REQUEST['type']??1;
        $res = Lottery::getLotteryList();
        foreach ($res as $key => &$val){
            if($val['lottery_num'] == 1){
                $val['name'] = $val['lottery_name'];
            }else{
//                if($val['lottery_name'] == '金币'){
                $val['lottery_num'] = $val['lottery_num']/100000000;
//                }
                $val['name'] = $val['lottery_name'].'+'.$val['lottery_num'];
            }
            $val['lottery_icon'] = $val['lottery_icon'];
        }
        $resule['code'] = 'ok';
        $resule['status'] = 200;
        $resule['data']['list'] = $res??[];
        $config       = Config::model();
        $limit_info = $config->getLimitInfo('user_ad_times');
        //$config->increaseValue('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $limit_info[0]]);
        $watchAdtimes = $config->getCache('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $limit_info[0]], true);
        $lostTimes = $limit_info['1'] - $watchAdtimes;

        $uid = $this->user->id;
        $data['uid'] = $uid;
        $lotteryTimes = Lottery::getLotteryTimes($data);

        $resule['data']['lostAdTimes'] = $lostTimes;
        $resule['data']['lotteryTimes'] = intval($lotteryTimes);
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $resule['data']['op_flag'] = $op_flag;
        }
        echo json_encode($resule);exit;
    }

}

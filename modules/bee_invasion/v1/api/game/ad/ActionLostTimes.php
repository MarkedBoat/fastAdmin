<?php

namespace modules\bee_invasion\v1\api\game\ad;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\lottery\Lottery;
use modules\bee_invasion\v1\model\role\RoleNote;
use modules\bee_invasion\v1\model\user\UserAd;

//观看广告
class ActionLostTimes extends GameBaseAction
{
    public function run()
    {
        $type = $this->inputDataBox->getStringNotNull('type');
        if($type == 2){
            $item_code = 'ad_saveSign_note';
            //判断补签次数
            $signCount = Sys::app()->db('dev')->setText("select count(*) as count from bi_user_sign where user_id=:user_id and cycle_day > 0 and is_countersign = 1")->bindArray(['user_id'=>$this->user->id])->queryRow();
            if($signCount['count'] >= 15){
                throw new \Exception('本次周期内补签已超过四次，无法继续补签');
            }
        }else{
            $item_code = 'ad_lottery_note';
        }
        $note_code = $this->inputDataBox->getStringNotNull('ad_note');
        RoleNote::verifyNoteCode($this->user->id, $item_code, $note_code);
        list($expires, $rand, $item_code_str, $user_id_str, $sign_str) = explode('#', $note_code);

        $config       = Config::model();
        $curr_ad_flag = $config->getCache('UserAdFlag', ['user_id' => $this->user->id]);
        if ($curr_ad_flag !== $note_code)
        {
            throw  new AdvError(AdvError::request_param_verify_fail, '凭证无效，请重新获取');
        }

        $adWatch = UserAd::model($this->user)->recordWatched($item_code, $note_code);
        if ($adWatch === false)
        {
            return $this->dispatcher->createInterruption(AdvError::user_note_has_used['detail'], '广告凭证无效', false, false);
        }

        $limit_info = $config->getLimitInfo('user_ad_times');
        //$config->increaseValue('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $limit_info[0]]);
        $watchAdtimes = $config->getCache('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $limit_info[0]], true);
        $lostAdtimes = $limit_info['1'] - $watchAdtimes;
        $ttl = ($expires - time());
        if($type == 1){
            //抽奖次数+n
            //获取广告配置
            $data['type'] = $type;
            $res = Lottery::getAdTimes($data);
            $date = date('Y-m-d');
            $cache = Sys::app()->params['cache_cfg']['UserLotteryNum']['key'].$this->user->id;
            $times = Sys::app()->redis('cache')->incrby($cache,$res['gift_num']);
        }
        $result['code'] = 'ok';
        $result['status'] = 200;
        $result['data']['lotteryTimes'] = $times??0;
        $result['data']['lostAdTimes'] = $lostAdtimes>0?$lostAdtimes:0;
        echo json_encode($result);exit;

    }
    //第一版
    public function test()
    {
        $data['type'] = $_REQUEST['type'];
        $hour = date('H');
        $date = date('Y-m-d');
        $uid = $this->user->id;
        //获取广告总观看次数上限
        $res = Lottery::getAdTimes($data);
        //获取当前观看次数
        $cacheName = 'lottery_times_'.$date.'_'.$res['start_time'].'-'.$res['end_time'].$uid;
        $times = Sys::app()->redis('cache')->get($cacheName);

        if(!$times){
            $times = 0;
        }
        if($times >= $res['times']){
            throw  new \Exception('观看广告已超出限制', 400);
        }
        //广告观看记录+1
        $adtime = Sys::app()->redis('cache')->incrby($cacheName,1);
        $lostAdtimes = $res['times'] - $adtime;
        //抽奖次数+n
        $date = date('Y-m-d');
        $cache = Sys::app()->params['cache_cfg']['UserLotteryNum']['key'].$uid;
        //$cache = 'lottery_times_'.$date.$uid;
        $times = Sys::app()->redis('cache')->incrby($cache,$res['gift_num']);

        $result['code'] = 'ok';
        $result['status'] = 200;
        $result['data']['lotteryTimes'] = $times??0;
        $result['data']['lostAdTimes'] = $lostAdtimes??0;
        echo json_encode($result);exit;

    }


}
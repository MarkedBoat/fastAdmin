<?php

namespace modules\bee_invasion\v1\api\game\stage;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\param\DataBox;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\rank\RoleStageScoreDao;
use modules\bee_invasion\v1\dao\game\role\RoleStatisDao;
use modules\bee_invasion\v1\dao\game\stage\StageReportDao;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\role\RoleNote;


class ActionReport extends GameBaseAction
{


    public function run()
    {
        $json = file_get_contents("php://input");
        if (empty($json))
        {
            throw new AdvError(AdvError::request_param_empty);
        }
        $databox                 = DataBox::getQueryParam($json);
        $channel_code            = $databox->getStringNotNull('channel_code');
        $channel_note            = $databox->getStringNotNull('channel_note');
        $channel                 = Channel::model()->getItemByCode($channel_code);
        $stage_index             = $databox->getIntNotNull('stage_index');
        $event_name              = $databox->getStringNotNull('event');
        $report_dao              = StageReportDao::model();
        $report_dao->user_id     = $this->user->id;
        $report_dao->report_data = $json;
        $report_dao->update_time = date('Y-m-d H:i:s', time());

        $errors = [];
        $map    = [
            'public_channel' => 'ad_channel_note',
            'vip1_channel'   => 'vip1_channel_note',
            'vip2_channel'   => 'vip2_channel_note',
            'vip3_channel'   => 'vip3_channel_note',
        ];


        list($expires, $rand, $item_code_type, $user_id_str, $sign_str) = explode('#', $channel_note);

        if (isset($map[$channel_code]))
        {
            if ($map[$channel_code] !== $item_code_type)
            {
                $errors[] = 'note_type_error';
                Sys::app()->addLog([$channel_note, $item_code_type, $map[$channel_code]], 'note_type_error  $channel_note !== $item_code_type');
            }
        }
        else
        {
            $errors[] = 'channel_error';
        }

        $verify_res = RoleNote::verifyNoteCode($this->user->id, $item_code_type, $channel_note);
        if ($verify_res['sign'] === 0)
        {
            $errors[] = 'note_invalid';
            Sys::app()->addLog($verify_res, 'note_invalid $verify_res[sign] === 0');
        }
        $curr_note_account = RoleNote::model()->setUser($this->user)->getAccount($item_code_type, true);
        $now_ts            = time();
        $time_left         = intval($expires) - $now_ts;
        $is_expired        = false;
        if ($time_left <= 0)
        {
            $is_expired = true;
            $errors[]   = 'note_timeout';
            Sys::app()->addLog($time_left, '$time_left <= 0');

        }

        Sys::app()->addLog(['status' => $curr_note_account->item_status, 'curr_code' => [$curr_note_account->item_code, $item_code_type], 'curr_note' => $curr_note_account->item_value, 'time_left' => $time_left], 'last_note_info');

        if ($curr_note_account->item_status >= Opt::noteStatus_useless)
        {
            $errors[] = 'note_used';
            Sys::app()->addLog([$curr_note_account->item_status, Opt::noteStatus_useless], '$curr_note_account->item_status >= Opt::noteStatus_useless');

        }
        else
        {
            if ($is_expired && in_array($event_name, ['game_over', 'stage_pass'], true))
            {
                Sys::app()->addLog($curr_note_account->item_status, 'update_note_status');
                $curr_note_account->item_status = RoleNote::noteStatus_used;
                $curr_note_account->update(true, false);
            }

        }

        if ($curr_note_account->item_value !== $channel_note)
        {
            $errors[] = 'note_not_match';
            Sys::app()->addLog([$curr_note_account->item_value, $channel_note], 'note_not_match $curr_note_account->item_value !== $channel_note');
        }

        $report_dao->has_exception = count($errors) ? Opt::YES : Opt::NOT;
        $report_dao->errors        = join(',', $errors);
        $report_dao->insert();
        // $curr_note_account->reloadData();


        $intersect = array_intersect(['note_used', 'note_type_error', 'channel_error', 'note_invalid'], $errors);
        Sys::app()->addLog($errors, '$errors');
        if (count($intersect))
        {
            throw new AdvError(AdvError::request_param_verify_fail, '请重新获取门票，在此之前，您将获取不到任何收益！', $intersect);
        }

        if (in_array($event_name, ['stage_pass']))
        {
            $statis_dao = RoleStatisDao::model()->findOneByWhere(['id' => $this->user->id], false);
            if (empty($statis_dao))
            {
                $statis_dao              = RoleStatisDao::model();
                $statis_dao->id          = $this->user->id;
                $statis_dao->stage_index = $stage_index;
                $res                     = $statis_dao->insert(false, false);
            }
            else
            {
                if (intval($statis_dao->stage_index) < $stage_index)
                {
                    $statis_dao->stage_index = $stage_index;
                    $statis_dao->update(false, false);
                }
            }
        }
        //{"score": 95, "duration": 146, "location": {"hash": "", "host": "localhost:7456", "href": "http://localhost:7456/", "port": "7456", "origin": "http://localhost:7456", "search": "", "hostname": "localhost", "pathname": "/", "protocol": "http:", "ancestorOrigins": {}}, "goldCoins": 66, "hang_time": 146, "user_token": "yangjinlong", "stage_index": 3}


        $data = [
            'record'      => $report_dao->id,
            'stage_score' => 0,
            'today_score' => 0,
            'rank'        => 0,
            'trailing'    => 0,
        ];
        //game_start  游戏开始
        //game_over  游戏结束
        //game_restart  重新开始
        //exit_game  退出游戏
        //role_die  角色死亡
        //role_relife 角色复活
        //game_pause 游戏暂停
        //game_goon 游戏继续
        if (in_array($event_name, ['role_die', 'game_over', 'stage_pass']))
        {
            $ymd         = date('Ymd', time());
            $score       = $databox->getInt('score');
            $duration    = $databox->getInt('duration');//玩的时间
            $goldCoins   = $databox->getInt('goldCoins');
            $hang_time   = $databox->getInt('hang_time');//整个时间,暂停+游玩
            $enemies_cnt = $databox->getInt('all_enemies');
            $killed_cnt  = $databox->getInt('score');
            if ($duration === 0)
            {
                $errors[] = 'duration_zero';
            }
            if ($enemies_cnt < $killed_cnt)
            {
                $errors[] = 'enemy_cnt';
            }
            if ($duration)
            {
                $score = floor($score * $stage_index * (300 / $duration) + ($goldCoins * $stage_index) * ($duration / $hang_time));
            }
            else
            {
                $score = -1;
            }

            $score_dao                = RoleStageScoreDao::model();
            $score_dao->channel_code  = $channel_code;
            $score_dao->ymd           = $ymd;
            $score_dao->stage_index   = $stage_index;
            $score_dao->score         = $score;
            $score_dao->user_id       = $this->user->id;
            $score_dao->channel_note  = $channel_note;
            $score_dao->report_id     = $report_dao->id;
            $score_dao->has_exception = count($errors) ? Opt::YES : Opt::NOT;
            $score_dao->errors        = join(',', $errors);
            $score_dao->setOnDuplicateKeyUpdate(['score' => $score]);
            $score_dao->insert(false);

            $report_dao->score_id = $score_dao->id;
            $report_dao->update(false);

            $data['stage_score'] = $score;
            $data['today_score'] = $score_dao->getUserScoreSum($channel_code, $ymd, $this->user->id);
            $key                 = "rank{$ymd}_{$channel_code}";
            Sys::app()->redis('cache')->zAdd($key, $data['today_score'], $this->user->id);


            $rank_info = RankTop::model()->getRankInfo($channel_code, $ymd, $this->user->id);
            if ($rank_info['user_rank'] === false)
            {
                return $this->dispatcher->createInterruption(AdvError::res_not_exist['detail'], '查询不到排名数据', $rank_info);
            }
            if ($rank_info['user_rank'] === 0)
            {

            }
            if ($rank_info['pre_score'] === false)
            {
                $rank_info['pre_score'] = $rank_info['user_score'] + rand(50, 100);
            }
            $data['trailing'] = $rank_info['pre_score'] - $rank_info['user_score'];
            $data['rank']     = $rank_info['user_rank'] + 1 + 5;

            //return ['pre_user_score' => $rank_info['pre_score'],];

        }
        Sys::app()->addLog(['errors' => $errors], 'xxx');
        return $data;

    }
}
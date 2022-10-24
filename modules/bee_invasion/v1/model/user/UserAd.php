<?php

namespace modules\bee_invasion\v1\model\user;


use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserAdHisDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\role\RoleNote;
use modules\bee_invasion\v1\model\task\AsyncTask;
use modules\bee_invasion\v1\model\user\User;

class UserAd
{
    private $user;
    private $limit_cycle_flag;
    private $limit_cycle_times;
    private $limit_cycle_watched = false;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param \modules\bee_invasion\v1\model\user\User $user
     * @return static
     */
    public static function model(User $user)
    {
        return new static($user);
    }

    /**
     * @throws AdvError
     */
    public function initLimitCycleInfo()
    {
        if (empty($this->limit_cycle_flag))
        {
            $config_model = new Config();
            $res          = $config_model->getLimitInfo('user_ad_times');
            if (empty($res))
            {
                throw  new AdvError(AdvError::data_info_unexpected, '配置信息有问题');
            }
            list($this->limit_cycle_flag, $this->limit_cycle_times) = $res;
        }
    }

    /**
     * @return bool|int|mixed|string
     * @throws AdvError
     */
    public function getWatchedTimes()
    {
        if ($this->limit_cycle_watched === false)
        {
            if (empty($this->limit_cycle_flag))
            {
                $this->initLimitCycleInfo();
            }
            $api_cache                 = ApiCache::model();
            $this->limit_cycle_watched = $api_cache->getCache('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $this->limit_cycle_flag], true);
        }
        return $this->limit_cycle_watched;

    }

    /**
     * @return static
     * @throws AdvError
     */
    public function increaseWatchedTimes()
    {
        $this->getWatchedTimes();
        ApiCache::model()->increaseValue('UserAdTimesLimit', ['user_id' => $this->user->id, 'date_sign' => $this->limit_cycle_flag]);
        $this->limit_cycle_watched = $this->limit_cycle_watched + 1;
        return $this;
    }


    /**
     * @param $ad_code
     * @return static
     * @throws AdvError
     */
    public function setAdFlag($ad_code)
    {
        ApiCache::model()->setCache('UserAdFlag', ['user_id' => $this->user->id], $ad_code);
        return $this;
    }

    /**
     * @return false|string
     * @throws AdvError
     */
    public function getAdFlag()
    {
        return ApiCache::model()->getCache('UserAdFlag', ['user_id' => $this->user->id]);
    }

    /**
     * @param $ad_use_for
     * @param $ad_note_code
     * @return static
     * @throws AdvError
     */
    public function verifyAdNoteCode($ad_use_for, $ad_note_code)
    {
        $res = RoleNote::verifyNoteCode($this->user->id, $ad_use_for, $ad_note_code);
        if (in_array(0, $res, true))
        {
            throw  new AdvError(AdvError::request_param_verify_fail, '凭证无效，可能是过期了，请重新获取');
        }

        if ($this->getAdFlag() !== $ad_note_code)
        {
            throw  new AdvError(AdvError::request_param_verify_fail, '凭证无效，请重新获取');
        }

        return $this;
    }


    public function recordWatched($ad_use_for, $ad_note_code)
    {
        $this->verifyAdNoteCode($ad_use_for, $ad_note_code);
        $this->increaseWatchedTimes();

        $user_ad_his             = new UserAdHisDao();
        $user_ad_his->user_id    = $this->user->id;
        $user_ad_his->ad_note    = $ad_note_code;
        $user_ad_his->ad_cycle   = $ad_note_code;
        $user_ad_his->ad_sn      = $ad_note_code;
        $user_ad_his->is_handled = Opt::NOT;
        $res                     = $user_ad_his->setInsertIgnore(true)->insert();
        if ($res)
        {
            $user_ad_his->findOneByWhere(['user_id' => $this->user->id, 'ad_note' => $ad_note_code]);
            $task              = new AsyncTask();
            $task->op          = AsyncTask::opAdReward4Inviter;
            $task->op_flag     = $ad_note_code;
            $task->op_param    = json_encode(['ad_note' => $ad_note_code, 'user_id' => $this->user->id]);
            $task->is_complete = Opt::NOT;
            $task->is_ok       = Opt::YES;
            $task->insert(false, false);
        }
        if (empty($user_ad_his->id))
        {
            Sys::app()->addError($user_ad_his->getOuterDataArray(), '记录广告失败');

            return false;
        }
        else
        {
          //  ApiCache::model()->getRedis()->rPush(json_encode($user_ad_his->getOuterDataArray()));
            return true;
        }
    }

    /**
     * 生产一个票据 code
     * @param string $use_for 用于
     * @param int $ttl 存活多少秒
     * @return array
     * @throws \Exception
     */
    public function generateAdNoteCode($use_for, $ttl)
    {
        $code = RoleNote::generateNoteCode($this->user->id, $use_for, $ttl);
        $this->initLimitCycleInfo();
        $this->getWatchedTimes();
        if ($this->limit_cycle_watched >= $this->limit_cycle_times)
        {
            throw  new AdvError(AdvError::res_reached_limit, '本时间内广告达到了上限');
        }
        $this->setAdFlag($code);
        return [
            'note' => [
                'ttl'  => $ttl,
                'code' => $code,
            ],
            'ad'   => [
                'limit'   => $this->limit_cycle_times,
                'watched' => $this->limit_cycle_watched,
            ],
        ];
    }


}
<?php

namespace modules\bee_invasion\v1\model\cache;


use models\common\db\ORM;
use models\common\db\Redis;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\TCache;


class  ApiCache
{
    use TCache;

    private $ttl         = 0;
    private $unique_flag = '';
    private $limit_times = 1;

    private $exist_times = [];
    private $now_ts      = 0;

    public static function model()
    {
        return new static();
    }


    public function newCd()
    {
        $this->ttl         = 0;
        $this->unique_flag = '';
        $this->limit_times = 1;
        $this->exist_times = [];
    }

    /**
     * @param $user_id
     * @param $opertion_flag
     * @param $ttl
     * @param $limit_times
     * @return static
     */
    public function setOperation($user_id, $opertion_flag, $ttl, $limit_times)
    {
        $this->ttl         = $ttl;
        $this->unique_flag = md5($user_id . '#' . $opertion_flag);
        $this->limit_times = $limit_times;
        $this->exist_times = [];
        return $this;
    }


    public function tryRecord($lock = true)
    {
        $lock_key = '';
        if ($lock)
        {
            $lock_key = md5('loc' . $this->unique_flag);
            if ($this->getRedis()->setnx($lock_key, 1))
            {
                $this->getRedis()->expire($lock_key, $this->ttl);
            }
            else
            {
                return false;
            }
        }
        $cache_data  = $this->getRedis()->get($this->unique_flag);
        $exist_times = empty($cache_data) ? [] : explode(',', $cache_data);

        $len          = count($exist_times);
        $this->now_ts = time();
        if ($len >= $this->limit_times)
        {
            $index = $this->limit_times - $len;
            if (intval($exist_times[$index]) <= $this->now_ts)
            {
                if (($index + 1) >= $len)
                {
                    $exist_times = [];
                }
                else
                {
                    $exist_times = array_slice($exist_times, $index + 1);
                }
            }
        }
        $this->exist_times = $exist_times;
        if (count($exist_times) >= $this->limit_times)
        {
            return $this->returnAndUnlock($lock_key, false);
        }
        $this->exist_times [] = time() + $this->ttl;

        $this->getRedis()->set($this->unique_flag, join(',', $this->exist_times), $this->ttl);
        Sys::app()->addLog(['key' => $this->unique_flag, 'val' => $this->exist_times, 'ttl' => $this->ttl], 'set cd');

        return $this->returnAndUnlock($lock_key, true);
    }

    public function getStatisInfo()
    {
        $cache_data   = $this->getRedis()->get($this->unique_flag);
        $exist_times  = empty($cache_data) ? [] : explode(',', $cache_data);
        $len          = count($exist_times);
        $this->now_ts = time();
        if ($len >= $this->limit_times)
        {
            $index = $this->limit_times - $len;
            if ($exist_times[$index] <= $this->now_ts)
            {
                if (($index + 1) >= $len)
                {
                    $exist_times = [];
                }
                else
                {
                    $exist_times = array_slice($exist_times, $index + 1);
                }
            }
        }
        $this->exist_times = $exist_times;
        if (count($exist_times) >= $this->limit_times)
        {
            return $this->returnAndUnlock(false, false);
        }
        $this->getRedis()->set($this->unique_flag, join(',', $this->exist_times), $this->ttl);
        Sys::app()->addLog(['key' => $this->unique_flag, 'val' => $this->exist_times,], 'reset cd');
        return $this->returnAndUnlock(false, true);
    }

    public function returnAndUnlock($lock_key, $return_res)
    {
        if ($lock_key)
        {
            $this->getRedis()->del($lock_key);
        }
        return $return_res;
    }

    /**
     *
     * @return array
     */
    public function getExistTimeAnchors()
    {
        return $this->exist_times;
    }

    public function getFastCdTime()
    {
        if (count($this->exist_times) === 0 || empty($this->exist_times[0]))
        {
            return 0;
        }
        $diff = $this->exist_times[0] - $this->now_ts;
        return $diff > 0 ? $diff : 0;

    }


}
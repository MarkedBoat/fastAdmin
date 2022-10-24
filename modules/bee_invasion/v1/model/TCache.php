<?php

namespace modules\bee_invasion\v1\model;


use models\common\db\ORM;
use models\common\db\Redis;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;


Trait  TCache
{
    /**
     * @return \Redis
     * @throws \Exception
     */
    public function getRedis()
    {
        return Sys::app()->redis('cache');
    }

    /**
     * @param $cache_key
     * @throws AdvError
     */
    public function checkCacheKey($cache_key)
    {
        if (strstr($cache_key, '{'))
        {
            throw  new AdvError(AdvError::code_error, 'cache key 变量未填充', [$cache_key]);
        }
    }

    /**
     * @param $cache_config_key
     * @param $var_map
     * @return mixed|string|string[]
     * @throws AdvError|\Exception
     */
    public function getCacheKey($cache_config_key, $var_map)
    {
        $key_tpl = Sys::app()->params['cache_cfg'][$cache_config_key]['key'];
        if (is_array($var_map))
        {
            foreach ($var_map as $k => $v)
            {
                //                if(!is_string($v)){
                //                    var_dump($cache_config_key,$var_map);
                //                    debug_print_backtrace();
                //                    die;
                //                }
                $key_tpl = str_replace("{\${$k}}", $v, $key_tpl);
            }
        }
        $this->checkCacheKey($key_tpl);;
        return $key_tpl;
    }


    /**
     * @param $cache_config_key
     * @param $var_map
     * @param $value
     * @throws AdvError|\Exception
     */
    public function setCache($cache_config_key, $var_map, $value)
    {
        $cfg     = Sys::app()->params['cache_cfg'][$cache_config_key];
        $key_tpl = $cfg['key'];
        if (is_array($var_map))
        {
            foreach ($var_map as $k => $v)
            {
                $key_tpl = str_replace("{\${$k}}", $v, $key_tpl);
            }
        }
        $this->checkCacheKey($key_tpl);;
        if (Sys::app()->isDebug())
        {
            Sys::app()->addLog(['cache_config_key' => $cache_config_key, 'var_map' => $var_map, 'key' => $key_tpl, 'value' => is_array($value) ? json_encode($value) : $value, 'ttl' => $cfg['ttl']], 'TCache->setCache()->$key_tpl');
        }
        Sys::app()->redis('cache')->set($key_tpl, is_array($value) ? json_encode($value) : $value, $cfg['ttl']);
    }

    public function mget($formated_keys)
    {
        if (Sys::app()->isDebug())
        {
            $res = Sys::app()->redis('cache')->mget($formated_keys);
            Sys::app()->addLog(['mset_array' => $formated_keys, 'res' => $res], 'TCache->mget()->$formated_keys');
            return $res;
        }
        return Sys::app()->redis('cache')->mget($formated_keys);
    }

    public function mset($mset_array)
    {
        if (Sys::app()->isDebug())
        {
            Sys::app()->addLog(['mset_array' => $mset_array], 'TCache->mset()->$mset_array');
        }
        return Sys::app()->redis('cache')->mset($mset_array);
    }


    /**
     * @param $cache_config_key
     * @param $var_map
     * @param bool $auto_conv
     * @param bool $return_cache_value
     * @return bool|int|mixed|string
     * @throws AdvError|\Exception
     */
    public function getCache($cache_config_key, $var_map, $auto_conv = true, $return_cache_value = false)
    {

        if (!isset(Sys::app()->params['cache_cfg'][$cache_config_key]))
        {
            throw  new AdvError(AdvError::code_error, '不存在配置' . $cache_config_key);
        }
        $cfg     = Sys::app()->params['cache_cfg'][$cache_config_key];
        $key_tpl = $cfg['key'];
        if (is_array($var_map))
        {
            foreach ($var_map as $k => $v)
            {
                $key_tpl = str_replace("{\${$k}}", $v, $key_tpl);
            }
        }
        $this->checkCacheKey($key_tpl);;
        if ($auto_conv)
        {
            // if(Sys::app())
            $cache_res = Sys::app()->redis('cache')->get($key_tpl);
            $res       = $cache_res;
            if (Sys::app()->getOptValue('no_cache'))
            {
                $cache_res = false;
            }
            if ($cache_res === false)
            {
                switch ($cfg['default'])
                {
                    case '{}':
                    case '[]':
                        $res = json_decode($cfg['default'], true);
                        break;
                    default:
                        $res = $cfg['default'];
                        break;
                }
            }
            else
            {
                switch ($cfg['default'])
                {
                    case '{}':
                    case '[]':
                        $res = json_decode($cache_res, true);
                        break;
                    case 0:
                        $res = intval($cache_res);
                        break;
                    case '':
                        break;
                    default:
                        $res = $cfg['default'];
                        break;
                }
            }
            if ($return_cache_value)
            {
                $res = [$cache_res, $res];
            }
        }
        else
        {
            $res = Sys::app()->redis('cache')->get($key_tpl);
            if (Sys::app()->getOptValue('no_cache'))
            {
                $res = false;
            }
        }
        if (Sys::app()->isDebug())
        {
            Sys::app()->addLog(['cache_config_key' => $cache_config_key, 'var_map' => $var_map, 'key' => $key_tpl, 'res' => $res], 'TCache->getCache()->$key_tpl');
        }
        return $res;
    }

    public function increaseValue($cache_config_key, $var_map)
    {
        $cfg     = Sys::app()->params['cache_cfg'][$cache_config_key];
        $key_tpl = $cfg['key'];
        if (is_array($var_map))
        {
            foreach ($var_map as $k => $v)
            {
                $key_tpl = str_replace("{\${$k}}", $v, $key_tpl);
            }
        }
        $this->checkCacheKey($key_tpl);;
        if (Sys::app()->isDebug())
        {
            Sys::app()->addLog(['cache_config_key' => $cache_config_key, 'var_map' => $var_map, 'key' => $key_tpl, 'ttl' => $cfg['ttl']], 'TCache->setCache()->$key_tpl');
        }
        Sys::app()->redis('cache')->incr($key_tpl);
        Sys::app()->redis('cache')->expire($key_tpl, $cfg['ttl']);
    }

    public function getCachedItemCodes()
    {
        return [$this->item_code, 'xxx'];
    }

    public function pushValueToQueue(\Redis $redis_object, $queue_key, $value)
    {
        $redis_object->rPush($queue_key, $value);
    }

    public function popQueue(\Redis $redis_object, $queue_key)
    {
        return $redis_object->lPop($queue_key);
    }


}
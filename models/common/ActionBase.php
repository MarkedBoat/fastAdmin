<?php

namespace models\common;

use models\Api;
use models\common\error\AdvError;
use models\common\param\DataBox;
use models\common\param\Params;
use models\common\sys\IDispatcher;
use models\common\sys\Sys;
use modules\sl\v1\model\Security;


/**
 * Class Action
 * @package models
 * 接口具体方法的抽你类
 */
abstract class ActionBase
{
    /**
     * @var DataBox
     */
    protected $inputDataBox = null;
    private   $__debug      = false;
    private   $__apiName    = '';
    protected $uri          = '';


    /**
     * @var $dispatcher IDispatcher|Api
     */
    protected $dispatcher;

    public    $requestMethods = [];       //允许的请求方法,空代表所有
    public    $dataSource     = 'REQUEST';//数据来源，TEXT,JSON_STRING,GET,POST,REQUEST
    protected $rawPostData    = '';

    public $version = 0;

    // if ($_SERVER['REQUEST_METHOD'] != "POST")
    //     Sys::app()->interruption()->setMsg('请使用post方法')->outBaseException();

    public function __construct($param = [])
    {

    }

    public function init()
    {

    }


    public function setInputDataBox(DataBox $dataBox)
    {
        $this->inputDataBox = $dataBox;
        return $this;
    }

    public function setDispatcher(IDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function initInputParam($param = [])
    {
        if (count($this->requestMethods))
        {
            if (!in_array($_SERVER['REQUEST_METHOD'], $this->requestMethods))
            {
                throw  new AdvError(AdvError::request_method_deny);
            }
        }
        switch ($this->dataSource)
        {
            case 'GET':
                $param = array_merge($param, $_GET);
                break;

            case 'POST':
                $param = array_merge($param, $_POST);
                break;
            case 'JSON_STRING':
                $this->rawPostData = file_get_contents('php://input');
                $tmp               = json_decode($this->rawPostData, true);
                if (!empty($tmp))
                {
                    $param = array_merge($param, $tmp);
                }
                break;
            case 'POST_ALL':
                $param             = array_merge($param, $_POST);
                $this->rawPostData = file_get_contents('php://input');
                $tmp               = json_decode($this->rawPostData, true);
                if (!empty($tmp))
                {
                    $param = array_merge($param, $tmp);
                }
                break;
            case 'TEXT':
                $this->rawPostData = file_get_contents('php://input');
                break;
            default:
                $param = array_merge($param, $_REQUEST);
                break;
        }
        $this->inputDataBox = new DataBox(array_merge($_GET, $param));
        $sys_opts           = $this->inputDataBox->tryGetArray('sys_opts');
        if (!empty($sys_opts))
        {
            Sys::app()->setOpts($sys_opts);
        }
        $this->__apiName = $this->inputDataBox->tryGetString('method');
        $this->init();
    }


    public function initCmd(DataBox $dataBox)
    {
        $this->setInputDataBox($dataBox);
        $this->run();
    }

    public static function getClassName()
    {
        return static::class;
    }


    /**
     * @return DataBox
     */
    public function getInputDataBox()
    {
        return $this->inputDataBox;
    }

    public abstract function run();


    public function debug()
    {
        $this->__debug = true;
    }

    public function isDebug()
    {
        return Sys::app()->isDebug() ? true : ($this->inputDataBox->tryGetString('lndebug') == 'x' ? true : false);
    }

    public function renderTpls($tpls, $jsVars)
    {
        $this->dispatcher->setOutType(Api::outTypeHtml);
        ob_start();
        foreach ($tpls as $tpl)
            if ($tpl)
            {
                $tpl = __ROOT_DIR__ . $tpl;
                if (!file_exists($tpl))
                    throw new AdvError(AdvError::code_error, '文件不存在', [$tpl]);
                include $tpl;
            }
        $contents = ob_get_contents();
        ob_end_clean();
        $jsScript = "<script>\n//server.outVarToJs\nvar serverData=".json_encode($jsVars).";\n</script>\n";

        return preg_replace('/<body(.*)?>/', "<body$1>\n" . $jsScript, $contents);
    }

    public function getRemoteIp()
    {
        return isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    }

    /**
     * 获取计算的一个缓存 key
     * @param $param_map
     * @return string
     * @throws \Exception
     */
    protected function getComputeCacheKey($param_map)
    {
        ksort($param_map);
        $str = (static::class) . '->';
        foreach ($param_map as $k => $v)
        {
            $str .= "{$k}:{$v},";
        }
        $md5 = md5($str);
        Sys::app()->addLog([$str, $md5], 'computeCacheKey');
        return $md5;
    }


    /**
     * 里面混合缓存逻辑，由子类实现，由子类判单缓存还能否使用
     * @return bool
     */
    public function isUseApiCache()
    {
        return false;
    }

    public function getCacheTtl()
    {
        return 3600;
    }

    /**
     * 获取api内部数据的更新时间 列表，这些数据是  构成数据的更新时间记录，如果有更新，那么接口也应该更新
     * @return array
     */
    public function getInnerDataUpdateTimeKeys()
    {
        return [];
    }

    public function getAcceptParamKeys()
    {
        return [];
    }

    public function exec()
    {
        if ($this->isUseApiCache())
        {
            $param_keys = $this->getAcceptParamKeys();
            $param      = [];
            foreach ($param_keys as $k)
            {
                $param[$k] = $this->inputDataBox->tryGetString($k);
            }
            $api_cache_key               = $this->getComputeCacheKey($param);
            $api_cache_update_time_key   = md5($api_cache_key . '_update_time');
            $inner_data_update_time_keys = $this->getInnerDataUpdateTimeKeys();
            if (Sys::app()->getOptValue('no_cache'))
            {
                $values = [false, false];
            }
            else
            {
                $keys   = array_merge([$api_cache_update_time_key, $api_cache_key], $inner_data_update_time_keys);
                $values = Sys::app()->redis('cache')->mget($keys);
            }
            if (Sys::app()->isDebug())
            {
                Sys::app()->addLog([[$api_cache_update_time_key, $values[0]], [$api_cache_key, $values[1]],], 'get_api_cache');
            }

            if ($values[0] !== false && $values[1] !== false)
            {
                $api_cache_create_time = intval($values[0]);
                $need_update           = false;
                foreach ($values as $tmp_i => $value)
                {
                    if ($tmp_i < 2 || $value === false)
                    {
                        continue;
                    }
                    if (intval($value) > $api_cache_create_time)
                    {
                        $need_update = true;//inner 的创建时间 比 接口缓存的创建时间晚
                        break;
                    }
                }
                if ($need_update === false)
                {
                    return json_decode($values[1], true);
                }
            }

            $api_data = $this->run();
            $now_ts   = time();
            Sys::app()->redis('cache')->set($api_cache_update_time_key, $now_ts, $this->getCacheTtl());
            Sys::app()->redis('cache')->set($api_cache_key, json_encode($api_data, JSON_UNESCAPED_SLASHES), $this->getCacheTtl());
            if (Sys::app()->isDebug())
            {
                Sys::app()->addLog([$api_cache_update_time_key => $now_ts, $api_cache_key => $api_data], 'set_api_cache');
            }
            $op_flag = $this->inputDataBox->tryGetString('op_flag');
            if ($op_flag)
            {
                $api_data['op_flag'] = $op_flag;
            }

            return $api_data;
        }
        else
        {
            $op_flag = $this->inputDataBox->tryGetString('op_flag');
            if ($op_flag)
            {
                return array_merge($this->run(), ['op_flag' => $op_flag]);
            }
            else
            {
                return $this->run();
            }
        }
    }

    public function setAction($action)
    {
        $this->uri = $action;
    }
}


<?php

namespace models\common\sys;

use models\common\db\MCD;
use models\common\db\MysqlCnn;
use models\common\db\MysqlPdo;
use models\common\error\Interruption;
use models\common\param\DataBox;
use models\common\param\WebRequest;

/**
 * Class Sys
 * @package models\common\sys
 *
 * @property WebRequest $webRequest
 */
class Sys
{
    private static $__case       = null;
    private        $__configs    = [];
    private        $__cases      = [];//实例
    private        $__isDebug    = false;
    public         $cache        = true;
    public         $params       = [];
    private        $__propertys  = [];
    private        $__logs       = [];
    private        $_forceLog    = false;
    private        $opts         = [];
    private        $log_filename = '';
    /**
     * @var $dispatcher IDispatcher|null
     */
    private $dispatcher;

    /**
     * @var $inputDataBox DataBox
     */
    private $inputDataBox;

    const cfgKeyRedis = 'redis';

    /**
     * @return Sys
     * @throws \Exception
     */
    public static function app()
    {
        if (is_null(self::$__case))
            throw  new \Exception('Sys 并未init~!');
        return self::$__case;
    }

    public static function isInit()
    {
        return is_null(self::$__case) ? false : true;
    }

    public static function init($configs)
    {
        if (isset($configs['password']))
        {
            fwrite(STDOUT, "----------------------------------\nEnter config password for data safe:");
            $psw = trim(fgets(STDIN));
            echo $configs['password'] === $psw ? "OK" : "FAIL!!!!!!! ";
            echo "\n----------------------------------\n";
            if ($configs['password'] !== $psw)
            {
                die;
            }
        }
        self::$__case            = new Sys();
        self::$__case->__configs = $configs;
        if (isset($configs['params']))
            self::$__case->params = $configs['params'];
        self::$__case->__isDebug = isset(self::$__case->params['is_debug']) && self::$__case->params['is_debug'] === true;
    }


    public function getConfig()
    {
        return $this->__configs;
    }

    public function setConfig($configs)
    {
        $this->__configs = $configs;
    }


    /**
     * @param IDispatcher $dispatcher
     * @return $this
     */
    public function setDispatcher(IDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return IDispatcher|null
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function setInputDataBox(DataBox $inputDataBox)
    {
        $this->inputDataBox;
    }

    /**
     * @param $redisKey
     * @return \Redis
     * @throws \Exception
     */
    public function redis($redisKey = 'default')
    {
        if (isset($this->__configs['redis'][$redisKey]))
        {
            if (!isset($this->__cases['redis']))
                $this->__cases['redis'] = [];
            if (!isset($this->__cases['redis'][$redisKey]))
            {
                try
                {
                    $this->__cases['redis'][$redisKey] = new \Redis();
                    $this->__cases['redis'][$redisKey]->connect($this->__configs['redis'][$redisKey]['host'], $this->__configs['redis'][$redisKey]['port']);
                    if (isset($this->__configs['redis'][$redisKey]['password']) && $this->__configs['redis'][$redisKey]['password'])
                        $this->__cases['redis'][$redisKey]->auth($this->__configs['redis'][$redisKey]['password']);
                    if (isset($this->__configs['redis'][$redisKey]['db']))
                        $this->__cases['redis'][$redisKey]->select($this->__configs['redis'][$redisKey]['db']);

                } catch (\Exception $exception)
                {
                    throw  new \Exception($exception->getMessage() . $this->__configs['redis'][$redisKey], $exception->getCode());
                }

            }
        }
        else
        {
            throw  new \Exception('没有配置redis信息', 400);
        }
        return $this->__cases['redis'][$redisKey];
    }

    /**
     * @return MCD
     * @throws \Exception
     */
    public function memcached()
    {
        if (isset($this->__cases['memcached']))
            return $this->__cases['memcached'];
        if (isset($this->__configs['memcached']))
        {
            if (!isset($this->__cases['memcached']))
            {
                try
                {
                    $this->__cases['memcached'] = new MCD($this->__configs['memcached']);
                } catch (\Exception $exception)
                {
                    throw  new \Exception($exception->getMessage(), $exception->getCode(), '');
                }
            }
        }
        else
        {
            throw  new \Exception('没有配置memcached信息', 400);
        }
        return $this->__cases['memcached'];
    }


    /**
     * @param $dbKey
     * @return MysqlCnn
     * @throws \Exception
     */
    public function db($dbKey)
    {
        if (isset($this->__configs['db'][$dbKey]))
        {
            if (!isset($this->__cases['db']))
                $this->__cases['db'] = [];
            if (!isset($this->__cases['db'][$dbKey]))
            {
                $this->__cases['db'][$dbKey] = MysqlCnn::configDb($this->__configs['db'][$dbKey]);
            }
        }
        else
        {
            Sys::app()->interruption()->setMsg('没有配置信息db:' . $dbKey . ' in [' . ENV_NAME . ' ]')->outError();
        }
        return $this->__cases['db'][$dbKey];
    }


    public function isDebug()
    {
        return $this->__isDebug;
    }

    /**
     * CLI 直接 设置  true   CGI 使用默认的看配置
     * @param $status
     */
    public function setDebug($status)
    {
        $this->__isDebug = $status;
    }

    /**
     * @return Interruption
     */
    public function interruption()
    {
        if (!isset($this->__cases['interruption']))
            $this->__cases['interruption'] = new Interruption();
        return $this->__cases['interruption'];
    }

    /**
     * @param bool $status
     * @return static
     */
    public function setForceLog($status = true)
    {
        $this->_forceLog = $status;
        return $this;
    }

    public function addLog($data, $title = false, $trace = true, $flag = '')
    {
        if ($this->__isDebug === false && $this->_forceLog === false)
            return false;
        $this->_forceLog = false;
        if ($trace)
        {
            $steps = debug_backtrace();
            $step  = [];
            foreach ($steps as $step)
            {
                if ($step['function'] === 'addLog')
                {
                    break;
                }
            }
            $this->__logs[] = [
                (($title ? $title : '') . '                     #==>' . $step['class'] . '->' . $step['function'] . '() #') . "#             " . $step['file'] . ':' . $step['line'] . "     ",

                $data,
            ];
        }
        else
        {
            $this->__logs[] = [$title, $data];
        }
        $this->logToFile($title, $data);
    }

    public function addError($data, $title = '', $trace = true)
    {
        return $this->addLog($data, $title ? "__ERROR__:{$title}" : '__ERROR__', $trace = true, 'addError');
    }


    public function getLogs()
    {
        return $this->__logs;
    }

    /**
     * @return static
     */
    public function clearLogs()
    {
        $this->__logs = [];
        return $this;
    }

    public function __get($name)
    {
        if (!isset($this->__propertys[$name]))
        {
            switch ($name)
            {
                case 'webRequest':
                    $this->__propertys['webRequest'] = new WebRequest();
                    break;
            }
        }
        return $this->__propertys[$name];
    }

    /**
     * 获取 选项值
     * @param string $opt_key 选项值，请用正向描述的  比如  no_cache
     * @return bool|mixed
     */
    public function getOptValue($opt_key)
    {
        return isset($this->opts[$opt_key]) ? $this->opts[$opt_key] : false;
    }

    public function setOpts($map)
    {
        $this->opts = $map;
        return $this;
    }

    public function addOpt($key, $val)
    {
        $this->opts[$key] = $val;
        return $this;
    }

    public function logToFile($title, $data)
    {
        $date = date('Y-m-d H:i:s', time());
        if (empty($this->log_filename))
        {
            $type = 'web';
            if ($this->getOptValue('cli'))
            {
                $type = 'cli';
            }
            list($ym, $d, $h) = explode('/', date('Ym/d/H'));

            $root     = Sys::app()->params['logDir'];
            $filename = "{$root}/{$type}/{$ym}/{$d}/{$h}.log";

            $d_dir = "{$root}/{$type}/{$ym}/{$d}";
            if (!file_exists($d_dir))
            {
                mkdir($d_dir, 0777, true);
                usleep(10);
            }
            if (!file_exists($d_dir))
            {
                throw new \Exception("创建目录失败  {$d_dir}");
            }

            if (!file_exists($filename))
            {
                file_put_contents($filename, $date . "->create<-\n", FILE_APPEND);
            }
            if (file_exists($filename))
            {
                $this->log_filename = $filename;
            }
            else
            {
                die("XXXXXXX");
            }
        }

        file_put_contents($this->log_filename, "{$date}->{$title}\n" . var_export($data, true) . "\n<-\n", FILE_APPEND);

    }

    public function logFile($log_dir_flag, $dir, $filename, $content)
    {
        $log_dir = $this->getOptValue($log_dir_flag);
        if (!$log_dir)
        {
            $root    = Sys::app()->params['logDir'];
            $log_dir = $dir ? "{$root}/{$log_dir_flag}/{$dir}" : "{$root}/{$log_dir_flag}";
            if (!is_dir($log_dir))
            {
                mkdir($log_dir, 0777, true);
            }
            $this->addOpt($log_dir_flag, $log_dir);
        }
        file_put_contents("{$log_dir}/{$filename}", $content);
    }
}


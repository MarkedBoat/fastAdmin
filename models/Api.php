<?php

namespace models;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\IDispatcher;
use models\common\sys\Sys;


class Api implements IDispatcher
{

    const outTypeJson = 'json';
    const outTypeHtml = 'html';
    const outTypeText = 'text';
    /**
     * @var ActionBase
     */
    private       $__action  = null;
    public static $hasOutput = false;//是否已经输出数据，如果已经输出，其它地方就不要再输出了
    private       $_outType  = 'json';


    public function run()
    {
        ob_start();
        $uri = trim(preg_replace('/\?(.*)?$/', '', $_SERVER['REQUEST_URI']), '/');

        $routes = Sys::app()->getConfig()['routes'];
        foreach ($routes as $fake_router => $true_route)
        {
           // var_dump('*', $uri, $fake_router, $fake_router === $uri);
            if ($fake_router === $uri)
            {
                $uri = $true_route;
                break;
            }
            else if (isset($fake_router[0]) && $fake_router[0] === '^')
            {
                $pattern = "/{$fake_router}/isU";
                preg_match_all($pattern, $uri, $ar);
                $res = preg_replace($pattern, $true_route, $uri);
                if ($uri !== $res)
                {
                    $uri = $res;
                    break;
                }

            }
        }
        $arr = explode('/', $uri);
        //var_dump($arr, $routes);
        if (count($arr) < 3)
        {
            die('uri格式错误，禁止访问');
        }
        $version         = $arr[1];
        $arr[1]          = explode('.', $version)[0];
        $arr[1]          .= '\\api';
        $lastIndex       = count($arr) - 1;
        $action          = $arr[$lastIndex];
        $arr[$lastIndex] = 'Action' . ucfirst($action);
        array_unshift($arr, 'modules');
        $actionClassPath = join('\\', $arr);
        try
        {
            $this->initAction($actionClassPath);
            $this->__action->version = floatval(substr($version, 1));
            $this->__action->setAction($uri);
            $this->__action->setDispatcher($this);
            $this->__action->initInputParam();
            $this->output();
        } catch (AdvError $advError)
        {
            $this->outAdvError($advError, 'try run');
        } catch (\Exception $e)
        {
            $this->outBaseException($e, 'try run');
        }
    }


    public function initAction($actionClassPath)
    {
        if (class_exists($actionClassPath))
        {
            $this->__action = new $actionClassPath();
        }
        else
        {
            Sys::app()->addLog('类不存在:' . $actionClassPath);
            throw new \Exception('方法不存在', 400);
        }
    }


    public function output()
    {
        try
        {
            $data = $this->__action->exec();
            $echo = '';
            if (Sys::app()->isDebug())
            {
                $echo = ob_get_contents();
            }

            ob_end_clean();

            if (isset($data['__isInterruption']) && $data['__isInterruption'] === true)
            {
                if (Sys::app()->params['errorHttpCode'] === 400)
                {
                    @header('HTTP/1.1 400 Not Found');
                    @header("status: 400 Not Found");
                }

                @header('content-Type:application/json;charset=utf8');
                $res = [
                    'status' => 400,
                    'code'   => $data['detail_code'],
                    'msg'    => $data['outer_msg'],
                    'data'   => $data['outer_data'],
                ];
                if (Sys::app()->isDebug())
                {
                    $res['__debugs'] = [
                        'echo'  => $echo,
                        'data'  => $data['debug_data'],
                        'log'   => Sys::app()->getLogs(),
                        'error' => error_get_last()
                    ];
                }
                self::$hasOutput = true;

                echo json_encode($res, JSON_UNESCAPED_SLASHES);
            }
            else
            {
                if ($this->_outType === self::outTypeJson)
                {
                    @header('content-Type:application/json;charset=utf8');

                    $data = [
                        'status' => 200,
                        'code'   => Sys::app()->interruption()->getCode(),
                        'data'   => $data,
                    ];
                    if (Sys::app()->isDebug())
                    {
                        $data['__debugs'] = [
                            'echo'  => $echo,
                            'log'   => Sys::app()->getLogs(),
                            'error' => error_get_last()
                        ];
                    }
                    self::$hasOutput = true;
                    echo json_encode($data, JSON_UNESCAPED_SLASHES);;
                }
                else if ($this->_outType === self::outTypeHtml)
                {
                    @header('content-Type:text/html;charset=utf8');
                    if (Sys::app()->isDebug())
                    {
                        echo $echo;
                    }
                    echo $data;
                }
                else if ($this->_outType === self::outTypeText)
                {
                    @header('content-Type:text/html;charset=utf8');
                    if (Sys::app()->isDebug())
                    {
                        echo $echo;
                    }
                    echo $data;
                }

            }


        } catch (AdvError $advError)
        {
            $this->outAdvError($advError, 'try output');
        } catch (\Exception $exception)
        {
            $this->outBaseException($exception, 'try output');
        }
    }

    public function outBaseException(\Exception $exception, $flag, $ext = [])
    {
        $echo = '';
        if (Sys::app()->isDebug())
        {
            $echo = ob_get_contents();
        }
        ob_end_clean();
        if (Sys::app()->params['errorHttpCode'] === 400)
        {
            @header('HTTP/1.1 400 Not Found');
            @header("status: 400 Not Found");
        }

        if ($this->_outType === self::outTypeText)
        {
            @header('content-Type:text/html;charset=utf8');

            echo "\nCODE:\n";
            echo Sys::app()->interruption()->getCode();
            echo "\ngetMessage:\n";
            echo $exception->getMessage();

            $lastError = error_get_last();

            if (Sys::app()->isDebug())
            {
                echo "\nECHO:\n";
                echo $echo;
                echo "\nFLAG:\n";
                echo "{$flag} outBaseException ";
                echo "\nFILE:\n";
                echo $exception->getFile() . '#' . $exception->getLine();
                echo "\nLOG:\n";
                var_dump(Sys::app()->getLogs());
                echo "\nTRACE:\n";
                echo $exception->getTraceAsString();
                echo "\nERROR:\n";
                var_dump($lastError);
            }

            self::$hasOutput = true;
        }
        else
        {
            @header('content-Type:application/json;charset=utf8');
            $data      = [
                'status' => 400,
                'code'   => Sys::app()->interruption()->getCode(),
                'msg'    => $exception->getMessage(),
            ];
            $lastError = error_get_last();
            $data      = array_merge($data, $ext);
            if (Sys::app()->isDebug())
            {
                $data['__debugs'] = [
                    'echo'  => $echo,
                    'flag'  => "{$flag} outBaseException ",
                    'file'  => $exception->getFile() . '#' . $exception->getLine(),
                    'log'   => Sys::app()->getLogs(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                    'error' => $lastError
                ];
            }
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);
            if ($lastError)
                self::lastError('', '', $lastError);
            self::$hasOutput = true;
            echo $json;
        }

    }

    public function outAdvError(AdvError $exception, $flag, $ext = [])
    {
        $echo = '';
        if (Sys::app()->isDebug())
        {
            $echo = ob_get_contents();
        }
        ob_end_clean();
        if (Sys::app()->params['errorHttpCode'] === 400)
        {
            @header('HTTP/1.1 400 Not Found');
            @header("status: 400 Not Found");
        }

        if ($this->_outType === self::outTypeText)
        {
            @header('content-Type:text/html;charset=utf8');

            echo "\nCODE:\n";
            echo Sys::app()->interruption()->getCode();
            echo "\ngetMessage:\n";
            echo $exception->getMessage();

            $lastError = error_get_last();

            if (Sys::app()->isDebug())
            {
                echo "\nECHO:\n";
                echo $echo;
                echo "\nFLAG:\n";
                echo "{$flag} outBaseException ";
                echo "\nFILE:\n";
                echo $exception->getFile() . '#' . $exception->getLine();

                echo "\nTRACE:\n";
                echo $exception->getTraceAsString();
                echo "\nERROR:\n";
                var_dump($lastError);
                echo "\nLOG:\n";
                var_dump(Sys::app()->getLogs());
            }

            self::$hasOutput = true;
        }
        else
        {
            @header('content-Type:application/json;charset=utf8');
            $data      = [
                'status' => 400,
                'code'   => $exception->getDetailCode(),
                'msg'    => $exception->getMessage(),
            ];
            $lastError = error_get_last();
            $data      = array_merge($data, $ext);
            if (Sys::app()->isDebug())
            {
                $data['__debugs'] = [
                    'echo'  => $echo,
                    'flag'  => "{$flag} outAdvError ",
                    'file'  => $exception->getFile() . '#' . $exception->getLine(),
                    'info'  => $exception->getDebugInfoData(),
                    'log'   => Sys::app()->getLogs(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                    'error' => $lastError
                ];
            }
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);
            if ($lastError)
                self::lastError('', '', $lastError);
            self::$hasOutput = true;
            echo $json;
        }
    }


    public static function lastError($msg, $code, $lastError)
    {
        $keys = ['Allowed memory size', 'Invalid UTF-8 sequence in argument'];
        $log  = false;
        foreach ($keys as $kw)
            if (strstr($lastError['message'], $kw))
            {
                $log = true;
                break;
            }
        if ($log)
        {
            try
            {
                $data['__debugs'][] = ['title' => '记录错误', 'data' => 'ok'];
            } catch (\Exception $e)
            {
                if (isset($data['__debugs']))
                    $data['__debugs'][] = ['title' => '记录错误失败', 'data' => $e->getMessage()];
            }
        }

    }

    public function outLastErrorAndExit()
    {

    }

    /**
     * @param $detail_code
     * @param $outer_msg
     * @param $outer_data
     * @param array $debug_data
     * @return array
     */
    public function createInterruption($detail_code, $outer_msg, $outer_data, $debug_data = [])
    {
        return [
            '__isInterruption' => true,
            'detail_code'      => $detail_code,
            'outer_msg'        => $outer_msg,
            'outer_data'       => $outer_data,
            'debug_data'       => $debug_data
        ];
    }

    public function setOutType($type)
    {
        $this->_outType = $type;
    }

    public function getOutType()
    {
        return $this->_outType;
    }


}
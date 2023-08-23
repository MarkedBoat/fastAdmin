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

    const actoinStatusInit   = 'init';
    const actoinStatusRunned = 'runed';


    /**
     * @var ActionBase
     */
    private       $__action  = null;
    public static $hasOutput = false;//是否已经输出数据，如果已经输出，其它地方就不要再输出了
    private       $_outType  = 'text';

    private $actionStatus = '';
    private $interrupt_info;

    public function run()
    {
        ob_start();
        $uri = trim(preg_replace('/\?(.*)?$/', '', $_SERVER['REQUEST_URI']), '/');

        $routes = Sys::app()->setDispatcher($this)->addOpt('api', true)->getConfig()['routes'];
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
        $actionClassPath    = join('\\', $arr);
        $this->actionStatus = self::actoinStatusInit;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
        {
            $this->setOutType(self::outTypeJson);
        }
        try
        {
            $this->initAction($actionClassPath);
            $this->__action->version = floatval(substr($version, 1));
            $this->__action->setAction($uri);
            $this->__action->setDispatcher($this);
            $this->__action->initInputParam();
        } catch (\Exception $e)
        {
            $this->outException($e, 'init');
            return false;
        }

        $this->actionStatus = self::actoinStatusRunned;
        try
        {
            $this->__action->init();
            $this->output();
        } catch (AdvError $advError)
        {
            //交由action 进行控制 进行处理
            if ($this->__action->handleAdvError($advError) === false)
            {
                $this->outException($advError, 'run');
            }
        } catch (\Exception $e)
        {
            $this->outException($e, 'run');
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

            if (isset($data['__isInterruption']) && $data['__isInterruption'] === true)
            {
                $this->outResult($this->getErrorInfo('action.output'));
            }
            else
            {
                $echo = '';
                if (Sys::app()->isDebug())
                {
                    $echo = ob_get_contents();
                }
                ob_end_clean();

                if ($this->_outType === self::outTypeJson)
                {
                    @header('content-Type:application/json;charset=utf8');

                    $data = [
                        'status' => 200,
                        'code'   => Sys::app()->interruption()->getCode(),//准备改造掉，没有时间，暂时不理会
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
                    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);;
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
                    @header('content-Type:text/plain;charset=utf8');
                    if (Sys::app()->isDebug())
                    {
                        echo $echo;
                    }
                    echo $data;
                }

            }

        } catch (\Exception $exception)
        {
            $this->outException($exception, 'try output');
        }
    }


    public function getErrorInfo($flag, $ext = [])
    {
        if (isset($this->interrupt_info['__isInterruption']))
        {
            $res = [
                'status' => 400,
                'code'   => $this->interrupt_info['detail_code'],
                'msg'    => $this->interrupt_info['outer_msg'],
                'data'   => $this->interrupt_info['outer_data'],
            ];
        }
        else
        {
            $res = [
                'status' => 400,
                'code'   => 'error',
                'msg'    => '',
                'data'   => false,
            ];
        }
        $res = array_merge($res, $ext);
        if (Sys::app()->isDebug())
        {
            $res['__debugs'] = [
                'echo'  => ob_get_contents(),
                'flag'  => "{$flag} outException ",
                'file'  => false,
                'data'  => isset($this->interrupt_info['debug_data']) ? $this->interrupt_info['debug_data'] : false,
                'log'   => Sys::app()->getLogs(),
                'trace' => false,
                'error' => error_get_last()
            ];
        }
        return $res;
    }


    public function getBaseExceptionInfo(\Exception $exception, $flag, $ext = [])
    {
        $res = $this->getErrorInfo($flag, $ext);
        if (!isset($this->interrupt_info['__isInterruption']))
        {
            $res['msg'] = $exception->getMessage();
            if (Sys::app()->isDebug())
            {
                $res['__debugs']['file']  = $exception->getFile() . '#' . $exception->getLine();
                $res['__debugs']['trace'] = explode("\n", $exception->getTraceAsString());
            }
        }
        return $res;
    }

    public function outResult($res)
    {
        ob_end_clean();
        if (Sys::app()->params['errorHttpCode'] === 400)
        {
            @header('HTTP/1.1 400 Not Found');
            @header("status: 400 Not Found");
        }

        if ($this->_outType === self::outTypeText)
        {
            @header('content-Type:text/plain;charset=utf8');
            echo "\nCODE:\n{$res['code']}\n";
            echo "\ngetMessage:\n{$res['msg']}\n";
            if (isset($res['__debugs']))
            {
                echo "\nECHO:\n{$res['__debugs']['echo']}\n";
                echo "\nFLAG:\n{$res['__debugs']['flag']}\n";
                echo "\nFILE:\n{$res['__debugs']['file']}\n";
                echo "\nLOG:\n";
                var_dump($res['__debugs']['log']);
                echo "\nTRACE:\n{$res['__debugs']['trace']}\n";
                echo "\nERROR:\n";
                var_dump($res['__debugs']['error']);
            }
        }
        else
        {
            @header('content-Type:application/json;charset=utf8');
            echo json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        self::$hasOutput = true;

    }

    public function outException(\Exception $exception, $flag, $ext = [])
    {
        $this->outResult($this->getBaseExceptionInfo($exception, $flag, $ext));
    }


    public function outLastErrorAndExit()
    {

    }


    public function createInterruptionInfo($detail_code, $outer_msg, $outer_data, $debug_data = [])
    {
        $this->interrupt_info = [
            '__isInterruption' => true,
            'detail_code'      => $detail_code,
            'outer_msg'        => $outer_msg,
            'outer_data'       => $outer_data,
            'debug_data'       => $debug_data
        ];
        return $this->interrupt_info;

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
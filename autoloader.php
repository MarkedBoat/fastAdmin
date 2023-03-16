<?php

function lastError()
{
    if (\models\Api::$hasOutput)
        return false;
    $d = error_get_last();
    if ($d)
    {
        ob_end_clean();
        // if( \models\common\sys\Sys::app()->params['errorHttpCode']===400){
        @header('HTTP/1.1 400 Not Found');
        @header("status: 400 Not Found");
        //}

        @header('content-Type:text/json;charset=utf8');
        $data = ['status' => 400, 'code' => 'code_error_', 'msg' => '服务器错误',];
        if (\models\common\sys\Sys::isInit())
        {
            if (\models\common\sys\Sys::app()->isDebug())
            {
                $d['message']    = explode("\n", $d['message']);
                $data['__debug'] = [
                    'out'   => __CLASS__ . '==>' . __METHOD__ . '() ##' . __LINE__,
                    'log'   => \models\common\sys\Sys::app()->interruption()->getLogs(),
                    'error' => $d
                ];
            }
            echo json_encode($data);
        }
        else
        {
            if (__KL_DEBUG__ === 'yes')
            {
                var_dump($d);
            }

        }

    }
}

register_shutdown_function('lastError');

/*
 * regist autoloader
 */
spl_autoload_register(function ($class)
{
    if ($class && !class_exists($class))
    {
        $file = str_replace('\\', '/', $class);
        $file .= '.php';

        if (file_exists($file))
        {
            include $file;
        }
        else
        {
            $file = __DIR__ . '/' . $file;
            if (file_exists($file))
            {
                include $file;
            }
        }
    }
});
// array_merge 会覆盖，这个不会覆盖
function merge_conf_just_fill()
{
    $params    = func_get_args();
    $array_len = count($params);
    if ($array_len === 1)
    {
        return $params[0];
    }
    else if ($array_len === 2)
    {

        foreach ($params[1] as $k => $v)
        {
            if (isset($params[0][$k]))
            {
                if (is_array($params[0][$k]) && is_array($v))
                {
                    $params[0][$k] = merge_conf_just_fill($params[0][$k], $v);
                }
                else
                {
                    //$params[0][$k] = $v;
                }
            }
            else
            {
                $params[0][$k] = $v;
            }
        }
        return $params[0];
    }
    else
    {
        $res = [];
        foreach ($params as $i => $array)
        {
            $res = merge_conf_just_fill($res, $array);
        }
        return $res;
    }
}

function merge_conf_with_cover()
{
    $params    = func_get_args();
    $array_len = count($params);
    if ($array_len === 1)
    {
        return $params[0];
    }
    else if ($array_len === 2)
    {

        foreach ($params[1] as $k => $v)
        {
            if (isset($params[0][$k]))
            {
                if (is_array($params[0][$k]) && is_array($v))
                {
                    $params[0][$k] = merge_conf_with_cover($params[0][$k], $v);
                }
                else if ($params[0][$k] === $v)
                {
                    //var_dump([$params[0][$k],$v]);
                }else{
                    $params[0][$k] = $v;
                }
            }
            else
            {
                $params[0][$k] = $v;
            }
        }
        return $params[0];
    }
    else
    {
        $res = [];
        foreach ($params as $i => $array)
        {
            $res = merge_conf_with_cover($res, $array);
        }
        return $res;
    }
}
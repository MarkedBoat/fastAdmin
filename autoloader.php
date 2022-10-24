<?php

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
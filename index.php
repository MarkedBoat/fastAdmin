<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

defined('__INDEX_DIR__') or define('__INDEX_DIR__', __DIR__);
defined('__ROOT_DIR__') or define('__ROOT_DIR__', __DIR__);

defined('__HOST__') or define('__HOST__', $_SERVER['HTTP_HOST']);


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
}

register_shutdown_function('lastError');
require 'autoloader.php';

require 'config/env/hosts_map.php';

(new \models\Api())->run();



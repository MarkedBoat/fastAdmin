<?php
/**
 * 域名 使用对应的配置文件
 */
$host        = __HOST__;
$configFiles = [
    'dev.aiqingyinghang.com:2051' => 'dp_dev',

];
if (isset($configFiles[$host]))
{
    $file = __ROOT_DIR__ . "/config/env/{$configFiles[$host]}.php";
    if (is_file($file) && is_readable($file))
    {
        $config = require $file;
        \models\common\sys\Sys::init($config);
    }
    else
    {
        die("config file not exist:{$file}");
    }
}
else
{
    die("domain has not configed:{$host}");
}
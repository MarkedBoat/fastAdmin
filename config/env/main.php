<?php
/**
 * 域名 使用对应的配置文件
 */
$host        = __HOST__;
$configFiles = [
    'xx.com' => 'xxx',//域名 => 配置文件名,匹配不上，默认找default
];

if (isset($configFiles[$host]))
{
    $file = __ROOT_DIR__ . "/config/env/{$configFiles[$host]}.php";
}
else
{
    $file = __ROOT_DIR__ . "/config/env/default.php";
}


if (is_file($file) && is_readable($file))
{
    $config = require $file;
    \models\common\sys\Sys::init($config);
}
else
{
    die("host [{$host}] config file not exist: [{$file}] ");
}
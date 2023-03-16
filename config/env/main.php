<?php
/**
 * 域名 使用对应的配置文件
 */
$host        = __HOST__;
$configFiles = [
    'dev.aiqingyinghang.com:2051' => 'dp_dev',
    'devbg.kl.com'                => 'kl_dev',
    'default'                     => 'docker_lnmp'

];

if (isset($configFiles[$host]))
{
    $config_filename = $configFiles[$host];
}
else
{
    if (isset($configFiles['default']))
    {
        $config_filename = $configFiles['default'];
    }
    else
    {
        die("host [{$host}] not matched");
    }
}

$file = __ROOT_DIR__ . "/config/env/{$config_filename}.php";

if (is_file($file) && is_readable($file))
{
    $config = require $file;
    \models\common\sys\Sys::init($config);
}
else
{
    die("host [{$host}] config file [{$config_filename}] not exist");
}
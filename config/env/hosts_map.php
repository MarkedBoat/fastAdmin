<?php
/**
 * 域名 使用对应的配置文件
 */
$host        = __HOST__;
$configFiles = [
    'dev.aiqingyinghang.com:2026'         => 'bee_invasion_dev',
    'dev.aiqingyinghang.com:2041'         => 'bee_invasion_dev',
    'dev.aiqingyinghang.com:2042'         => 'bee_invasion_dev',
    'dev.aiqingyinghang.com:2043'         => 'bee_invasion_test',
    'dev.aiqingyinghang.com:2044'         => 'bee_invasion_yangjl',
    'dev.aiqingyinghang.com:2045'         => 'bee_invasion_dev',
    'dev.aiqingyinghang.com:2046'         => 'bee_invasion_test',
    'beeinvasion.aiqingyinghang.com:2020' => 'bee_invasion_prod',
    'beeinvasion.aiqingyinghang.com:2021' => 'bee_invasion_prod',
    'beeinvasion.aiqingyinghang.com:2026' => 'bee_invasion_prod',

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
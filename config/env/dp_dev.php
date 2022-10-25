<?php
defined('ENV_NAME') or define('ENV_NAME', 'bee_invasion_dev');
//徐亚洲
$dev_cfg = [
    'connectionString' => 'mysql:host=mysql8_server;port=3306;dbname=dev_bg',
    'username'         => 'root',
    'password'         => 'Mysql!',
    'charset'          => 'utf8',
    'readOnly'         => true,
    'attributes'       => [
        \PDO::ATTR_TIMEOUT => 1
    ]
];


return array_merge_recursive(include __ROOT_DIR__ . '/config/env/common_param.php', [
    'db'    => [
        'dp' => $dev_cfg,
    ],
    'redis' => [
        'default' => ['host' => 'redis_server', 'port' => 6379, 'password' => '', 'db' => 0],
        'cache'   => ['host' => 'redis_server', 'port' => 6379, 'db' => 0],
    ],

    'params' => [
        'debugSign'            => 'debug',
        'errorHttpCode'        => 200,
        'is_debug'             => true,
        'database_block_index' => 0,//数据库分区 index
        'secret_key'           => [
            'note_md5' => 'jUjRPjcllhk7jpoQsKtfhryO5td0UwPA',
        ],
        'com_project_api'      => [
            'duck_time' => 'https://duck-time.dev.aiqingyinghang.com:2023',
        ],

        'console' => [
            'phpPath'        => '/usr/local/bin/php',
            'hammerPath'     => '/var/www/html/bee-invasion-back-end/hammer.php',
            'logDir'         => '/var/www/html/bee-invasion-back-end/log/cmd',
            'webFileDir'     => '/data/upload/cli_out',
            'root_cmd_queue' => 'root_cmd_queue',
            'tasks'          => [

            ]
        ],

    ],
]);
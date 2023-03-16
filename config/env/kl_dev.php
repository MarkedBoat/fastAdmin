<?php
defined('ENV_NAME') or define('ENV_NAME', 'kl_dev');
//徐亚洲
$dev_cfg = [
    'connectionString' => 'mysql:host=127.0.0.1;port=3306;dbname=dev_bg',
    'username'         => 'root',
    'password'         => 'Mysql!',
    'charset'          => 'utf8',
    'readOnly'         => true,
    'attributes'       => [
        \PDO::ATTR_TIMEOUT => 1
    ]
];

$fast_bg_cfg = [
    'connectionString' => 'mysql:host=mysql8_server;port=3306;dbname=fast_bg',
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
        '_sys_' => $fast_bg_cfg,
        'dp'    => $dev_cfg,
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
        //后台 系统设置
        'sys_setting'          => [
            'db' => [
                '$sys_dbname'       => 'fast_bg',
                'tableNameFakeCode' => [

                    //user|admin
                    '$user_admin_tableName'      => 'bg_admin',
                    //rbac
                    '$rbac_role_tableName'       => 'bg_rbac_role',
                    '$rbac_task_tableName'       => 'bg_rbac_task',
                    '$rbac_action_tableName'     => 'bg_rbac_action',
                    '$rbac_roleTask_tableName'   => 'bg_rbac_role_task',
                    '$rbac_taskAction_tableName' => 'bg_rbac_task_action',
                    '$rbac_menu_tableName'       => 'bg_rbac_menu',
                    '$rbac_roleMenu_tableName'   => 'bg_rbac_role_menu',
                    '$rbac_userRole_tableName'   => 'bg_rbac_user_role',
                    //dbdata
                    '$dbdata_dbconf_tableName'   => 'bg_db_dbconf',
                    '$dbdata_table_tableName'    => 'bg_db_table',
                    '$dbdata_column_tableName'   => 'bg_db_column',

                ],
            ]
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
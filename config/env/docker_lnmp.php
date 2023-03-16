<?php
defined('ENV_NAME') or define('ENV_NAME', 'lnmp');
//徐亚洲
$dev_cfg = [
    'connectionString' => 'mysql:host=127.0.0.1;port=3306;dbname=kl_dev_bg',
    'username'         => 'root',
    'password'         => 'Password@Mysql8',
    'charset'          => 'utf8mb4',
    'readOnly'         => true,
    'attributes'       => [
        \PDO::ATTR_TIMEOUT => 1
    ]
];


return merge_conf_with_cover(include __ROOT_DIR__ . '/config/env/common_param.php', [
    'db'    => [
        '_sys_' => $dev_cfg,
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
    'routes' => [
        'docker_route_test'    => 'docker/test',
        'duplicate_route_test' => 'docker/test',
        '^(.*)\/(\w+).html$'   => '$1/render_$2',
        ''                     => '_dp/v1/index/render_index',
        'dp/login'             => '_dp/v1/user/render_login',
        'dp/resetPsw'          => '_dp/v1/user/render_resetPassword',
        'dp/index'             => '_dp/v1/index/render_index',

        'dp/rbac/config' => '_dp/v1/rbac/render_config',
        'dp/menu/tree'   => '_dp/v1/rbac/render_menuTree',

        'dp/dbdata/dbconfs'   => '_dp/v1/dbdata/render_dbconfs',
        'dp/dbdata/tables'    => '_dp/v1/dbdata/render_tables',
        'dp/dbdata/columns'   => '_dp/v1/dbdata/render_columns',
        'dp/dbdata/tableRows' => '_dp/v1/dbdata/render_tableRows',

    ],
]);
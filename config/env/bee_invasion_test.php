<?php
defined('ENV_NAME') or define('ENV_NAME', 'bee_invasion_dev');
//徐亚洲
$dev_cfg = [
    'connectionString' => 'mysql:host=mysql8_server;port=3306;dbname=bee_invade',
    'username'         => 'root',
    'password'         => 'Mysql!',
    'charset'          => 'utf8',
    'readOnly'         => true,
    'attributes'       => [
        \PDO::ATTR_TIMEOUT => 1
    ]
];

$prod_cfg = [
    'connectionString' => 'mysql:host=112.126.70.53;port=3306;dbname=api_aqgs_aiqingy',
    'username'         => 'api_aqgs_aiqingy',
    'password'         => 'XBYrTZTTbXEYr5rT',
    'charset'          => 'utf8',
    'readOnly'         => true,
    'attributes'       => [
        \PDO::ATTR_TIMEOUT => 1
    ]
];


return array_merge_recursive(include __ROOT_DIR__ . '/config/env/common_param.php', [
    'db'    => [
        'bee_invade' => $dev_cfg,
        'dev'        => $dev_cfg,
        'prod'       => $prod_cfg,
    ],
    'redis' => [
        'default' => ['host' => 'redis_server', 'port' => 6379, 'password' => '', 'db' => 0],
        'cache'   => ['host' => 'redis_server', 'port' => 6379, 'db' => 0],
        'pay'     => ['host' => 'redis_server', 'port' => 6379, 'db' => 1],
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
        'pay'                  => [
            'pay_page_domain'                    => 'http://dev.aiqingyinghang.com:2043',
            'adapay_app_id'                      => 'app_4721a7da-805e-47d6-94dc-e5404bd5f19b',
            'secret_md5'                         => 'dlPkruu0BJsp8K696Dfev1zO1nWxjppd',
            'game_order_payed_queue'             => 'game_order_payed_queue',//游戏订单异步通知队列key
            'plat_order_notify_queue'            => 'plat_order_notify_queue',//支付异步通知队列key
            'partner_order_notify_queue_prefix'  => 'partner_order_notify_queue',//异步通知第三方的队列key
            'partner_order_notify_status_prefix' => 'partner_order_notify_status',//异步通知第三方的队列key
            'plat_order_prefix'                  => 'YmdH000',

        ],
        'bee_invasion_pay'     => [
            'partner_code'     => 'bee_invasion',
            'private_key_file' => __ROOT_DIR__ . '/config/file/pay.private.key',
            'public_key_file'  => __ROOT_DIR__ . '/config/file/pay.public.key',
        ],
        'wxConfig'             => [
            'appId'     => 'wx15516f9b4569eb40',//绑定的小程序appid
            'appSercet' => '397e4b9324637b7a088178b1c51db9b3',
            'transUrl'  => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers',
            'mchId'     => '1618264944',//商户号
            'paySecret' => 'a278d0b302ae1a04e3ce1c519c492eb5',
        ],
        'checkCode'            => [
            'smscode' => '1111',
            'imgcode' => '1111'
        ],
        'cache_cfg'            => [
            //ac 账户
            //arm 装备信息
            //atr  attrs 属性
            //c -> cache
            //cg -> goods / cg 一次性消耗品
            //cfg -> config
            //ch -> channel
            //e -> equipment 装备
            //i -> item 代表是【单条】 信息的说明
            //inf -> info 代表里面是row
            //l  -> list 代表里面存的是数组  [ ],可能是 rows/pks/codes
            //n note 凭据
            //p perk 技能
            //r -> role 角色
            //tl -> times limit 次数限制
            //u -> user  用户
            'AdapayOrderOpenInfo'     => ['key' => 'adapay_{$open_order_id}', 'default' => '{}', 'ttl' => 86400],
            'CgItemCodes'             => ['key' => 'cg_item_codes', 'default' => '[]', 'ttl' => 3600],
            'CgInfo'                  => ['key' => 'cg_{$item_code}', 'default' => '{}', 'ttl' => 3600],//item_code 为索引
            'ChangeFlagUserCg'        => ['key' => 'cf_uc_{$user_id}', 'default' => 0, 'ttl' => 7200],
            'ChangeFlagUserCurrency'  => ['key' => 'cf_ucg_{$user_id}', 'default' => 0, 'ttl' => 7200],
            'ChangeFlagUserEquipment' => ['key' => 'cf_ue_{$user_id}', 'default' => 0, 'ttl' => 7200],
            'ChangeFlagUserObject'    => ['key' => 'cf_object_{$user_id}', 'default' => 0, 'ttl' => 7200],
            'ChannelInfo'             => ['key' => 'ch_{$item_code}', 'default' => '{}', 'ttl' => 3600],//分区/频道
            'ChannelItemCodes'        => ['key' => 'ch_codes', 'default' => '{}', 'ttl' => 3600],//
            'ConfigInfo'              => ['key' => 'cfg_{$item_code}', 'default' => '{}', 'ttl' => 3600],//配置信息
            'ConfigItemCodes'         => ['key' => 'cfg_codes', 'default' => '{}', 'ttl' => 3600],//
            'CurrencyItemCodes'       => ['key' => 'curr_item_codes', 'default' => '[]', 'ttl' => 3600],
            'CurrencyInfo'            => ['key' => 'crur_{$item_code}', 'default' => '{}', 'ttl' => 3600],//item_code 为索引 ,
            'EquipmentItemCodes'      => ['key' => 'equip_item_codes', 'default' => '[]', 'ttl' => 3600],
            'EquipmentInfo'           => ['key' => 'equip_{$item_code}', 'default' => '{}', 'ttl' => 3600],
            'NoteItemCodes'           => ['key' => 'note_item_codes', 'default' => '[]', 'ttl' => 3600],
            'NoteInfo'                => ['key' => 'note_{$item_code}', 'default' => '{}', 'ttl' => 3600],
            'NoticeInfo'              => ['key' => 'notice_{$pk}', 'default' => '{}', 'ttl' => 3600],
            'NoticeLastedPks'         => ['key' => 'notice_lasted_pks', 'default' => '[]', 'ttl' => 3600],
            'ObjectInfo'              => ['key' => 'obj_{$item_code}', 'default' => '{}', 'ttl' => 3600],
            'ObjectItemCodes'         => ['key' => 'obj_item_codes', 'default' => '[]', 'ttl' => 3600],
            'OrderUnique'             => ['key' => 'order_unique_{$flag}', 'default' => '{}', 'ttl' => 3600],
            'PartnerInfo'             => ['key' => 'partner_{$pk}', 'default' => '{}', 'ttl' => 3600],//注意，不只是pk ，更可能是 src code
            'PartnerLastedPks'        => ['key' => 'partner_lasted_pks', 'default' => '[]', 'ttl' => 3600],
            'PerkItemCodes'           => ['key' => 'perk_item_codes', 'default' => '[]', 'ttl' => 3600],
            'PerkInfo'                => ['key' => 'perk_{$item_code}', 'default' => '{}', 'ttl' => 3600],//item_code 为索引
            'PriceItemInfo'           => ['key' => 'price_{$pk}', 'default' => '{}', 'ttl' => 3600],
            'PriceItemLastedPks'      => ['key' => 'price_lasted_pks', 'default' => '[]', 'ttl' => 3600],
            'RankTopDaily'            => ['key' => 'rank_top_daily_{$channel_code}_{$date_index}', 'default' => '{}', 'ttl' => 3600],//时间 为索引
            'RoleArm'                 => ['key' => 'role_arm_{$user_id}', 'default' => '{}', 'ttl' => 3600],//user_id  为索引,装备信息
            'RoleEquipemtAccountInfo' => ['key' => 'r_eq_ac_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//
            'RoleEquipemtAmount'      => ['key' => 'r_eq_a_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//user_id + ite
            'RoleNoteAccountInfo'     => ['key' => 'rn_{$user_id}_{$item_code}', 'default' => '{}', 'ttl' => 3600],//用户的某种票据情况
            'RoleNoteInfo'            => ['key' => 'rn_{$user_id}_{$item_code}', 'default' => '{}', 'ttl' => 3600],//用户的某种票据情况
            'RoleNoteCurrent'         => ['key' => 'c_r_n_c_{$user_id}_{$item_code}', 'default' => '', 'ttl' => 3600],//用户的某种票据 当前是哪个
            'RolePerkTimes'           => ['key' => 'rpt_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//user_id + item_code 为索引
            'ScrollTextInfo'          => ['key' => 'scrltext_{$pk}', 'default' => '{}', 'ttl' => 3600],
            'ScrollTextLastedPks'     => ['key' => 'scrltext_lasted_pks', 'default' => '[]', 'ttl' => 3600],
            'UserAdFlag'              => ['key' => 'u_ad_{$user_id}', 'default' => '', 'ttl' => 300],//
            'UserAdTimesLimit'        => ['key' => 'u_ad_tl_{$user_id}_{$date_sign}', 'default' => 0, 'ttl' => 86400],//user_id + item_code 为索引
            'UserCgAccountInfo'       => ['key' => 'ucg_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//
            'UserCgAmount'            => ['key' => 'ucg_a_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//user_id + item_code 为索引
            'UserCurrencyAccountInfo' => ['key' => 'uc_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//
            'UserCurrencyAmount'      => ['key' => 'uc_a_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//user_id + item_code 为索引
            'UserDataStatisAttrs'     => ['key' => 'uds_atr_{$user_id}_{$item_code}', 'default' => 0, 'ttl' => 3600],//
            'UserInfo'                => ['key' => 'u_{$user_id}', 'default' => '{}', 'ttl' => 3600],//id 为索引
            'UserCheckCode'           => ['key' => 'c_u_check_code', 'default' => 0],//id 为索引 用户验证码缓存
            'UserPwdKey'              => ['key' => 'c_u_pwd_key', 'default' => 0],//秘钥
            'AdminUserPwdKey'         => ['key' => 'a_u_pwd_key', 'default' => 0],//后台秘钥
            'UserDayCodeSum'          => ['key' => 'c_u_code_sum_', 'default' => 0],//用户当天发送验证码数量
            'UserLotteryNum'          => ['key' => 'c_u_lottery_num_', 'default' => 0],//用户抽奖数量
            'UserLotteryList'         => ['key' => 'c_u_lottery_list', 'default' => 0],//抽奖奖品列表
            'UserloginCache'          => ['key' => 'c_u_login_cache', 'default' => 0],//用户登录缓存
            'UserSignConfig'          => ['key' => 'c_u_sign_config', 'default' => 0],//用户签到奖品配置
            'UserLoginToken'          => ['key' => 'c_u_login_token', 'default' => 0],//用户登录token
            'UserLoginTimes'          => ['key' => 'c_u_login_times_', 'default' => 0, 'ttl' => 5],//用户登录时间

        ],
        'console'              => [
            'phpPath'        => '/usr/local/bin/php',
            'hammerPath'     => '/var/www/html/bee-invasion-back-end/hammer.php',
            'logDir'         => '/var/www/html/bee-invasion-back-end/log/cmd',
            'webFileDir'     => '/data/upload/cli_out',
            'root_cmd_queue' => 'root_cmd_queue',
            'tasks'          => [
                'handle_adapay_notify' => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay handlePayedNotify',
                    'comment'   => '处理adapay异步通知',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7100,
                    'logstyle'  => ['Ymd', '', '>>'],
                ],
                'notify_partner_0s'    => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=0s',
                    'comment'   => '异步通知通知商户 0s 级别的',
                    'status'    => true,
                    'maxLimit'  => 3,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_2s'    => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 2s 级别的',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],

                'notify_partner_3s'   => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=3s',
                    'comment'   => '异步通知通知商户 3s 级别的',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_5s'   => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=5s',
                    'comment'   => '异步通知通知商户 5s 级别的',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_10s'  => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 10s 级别的',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_30s'  => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 30s 级别的',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_1min' => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 1min 级别的',
                    'status'    => true,
                    'maxLimit'  => 2,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_5min' => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 5min 级别的',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],

                'notify_partner_1h'  => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 1h 级别的',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'notify_partner_24h' => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'plat/adapay notifyPartner --time_flag=2s',
                    'comment'   => '异步通知通知商户 24 级别的',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],


                'git_deploy'                             => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'system/project scan',
                    'comment'   => '代码部署',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'kill_plan_by_id'                        => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'system/launcher killPlan',
                    'comment'   => '临时杀死任务',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'rank_generate'                          => [
                    'time'      => ['10 0 */1'],
                    'cmd'       => 'bee_invasion/rank  generateAndReward',
                    'comment'   => '生成排行榜',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'asyncTask_handleShoppingReward4Inviter' => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'bee_invasion/asyncTask  handleShoppingReward4Inviter',
                    'comment'   => '消费返现',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'asyncTask_handleAdReward4Inviter'       => [
                    'time'      => ['*/1 */1 */1'],
                    'cmd'       => 'bee_invasion/asyncTask  handleAdReward4Inviter',
                    'comment'   => '看广告返现',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'agent_count'                            => [
                    'time'      => ['1 1 *'],
                    'cmd'       => 'bee_invasion/agentCount  countMoney',
                    'comment'   => '代理区域消费统计',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
                'agent_pay'                              => [
                    'time'      => ['1 22 *'],
                    'cmd'       => 'bee_invasion/agentPay  pay',
                    'comment'   => '代理区域发放收益',
                    'status'    => true,
                    'maxLimit'  => 1,
                    'timeLimit' => 7200,
                    'logstyle'  => ['Y', 'md', '>>'],
                ],
            ]
        ],

    ],
]);
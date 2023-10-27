<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\tool;

use models\common\CmdBase;
use models\common\db\MysqlCnn;
use models\common\db\MysqlPdo;
use models\common\error\AdvError;
use models\common\param\DataBox;
use models\common\sys\Sys;
use models\ext\tool\File;

class CmdMysql extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function init()
    {
        parent::init();
    }


    public function exportJsonStrs()
    {
        //ssh -fN -L33061:localhost:3306 -p22 root@47.96.173.58
        // mysql -h127.0.0.1 --port=33061 -uroot -p'1qaz2wsx.mj'
        $config     = null;
        $configfile = $this->inputDataBox->getStringNotNull('configfile');
        if (file_exists($configfile) && is_readable($configfile))
        {
            $config = json_decode(file_get_contents($configfile), true);
            if (!is_array($config))
            {
                Sys::app()->interruption()->setMsg("配置文件内容错误 {$configfile} ")->setCode('error')->setDebugData($config)->outError();
            }
        }
        else
        {
            Sys::app()->interruption()->setMsg("配置文件不存在 {$configfile} ")->setCode('error')->outError();

        }
        var_dump($config);
        $dbconfigbox  = new DataBox($config);
        $ssh_cmds     = $dbconfigbox->getArray('ssh_cmds');
        $ssh_password = $dbconfigbox->getString('ssh_password');
        foreach ($ssh_cmds as $ssh_cmd)
        {
            exec("ps aux|grep '{$ssh_cmd}'|grep -v grep", $ar);
            var_dump(['ps_res' => $ar]);
            $code_dir = __ROOT_DIR__;
            if (count($ar) === 0)
            {
                exec("/bin/bash {$code_dir}/console/shell/ssh_exec.sh '{$ssh_cmd}' '{$ssh_password}'", $ar2);
                var_dump(['exce_res' => $ar2]);
            }
        }


        $bakupdir = $dbconfigbox->getStringNotNull('bakupdir');
        if (!(file_exists($bakupdir) && is_writeable($bakupdir)))
        {
            Sys::app()->interruption()->setMsg("备份目录不存在或者不可写： {$bakupdir} ")->setCode('error')->outError();
        }

        $bakdbs         = $dbconfigbox->getArrayNotNull('bakupdbs');
        $back_fullnames = [
            'game_analysis.daily_budget_analysis',
            'game_analysis.daily_cost_analysis',
            'game_analysis.daily_inventory_analysis',
            'game_analysis.daily_recent_total_recharge',
            'game_analysis.daily_user_analysis',
            'game_analysis.daily_user_retained_analysis',
            'game_analysis.realtime_user_analysis',
            'game_analysis.user_daily_active',
            'game_analysis.user_daily_pay',
            'game_analysis.user_exchange_records',
            'game_analysis.user_lottery_records_(\d{4})_(\d{2})',
            'game_analysis.user_present_records',
            'game_analysis.user_room_records_(\d{4})_(\d{2})',
            'test-admin.orders',
            'db-admin.orders',
        ];
        //        $back_fullnames = [
        //            'game_analysis.user_lottery_records_(\d{4})_(\d{2})',
        //            'game_analysis.user_room_records_(\d{4})_(\d{2})',
        //        ];

        $tmp_config = [
            'host'       => $dbconfigbox->getStringNotNull('host'),
            'port'       => $dbconfigbox->getIntNotNull('port'),
            'dbname'     => $dbconfigbox->getStringNotNull('dbname'),
            'username'   => $dbconfigbox->getStringNotNull('username'),
            'password'   => $dbconfigbox->getStringNotNull('password'),
            'charset'    => $dbconfigbox->getStringNotNull('charset'),
            'readOnly'   => true,
            'attributes' => [
                \PDO::ATTR_TIMEOUT => 600
            ]
        ];
        //Sys::app()->addLog($dev_cfg);
        $dbcnn = MysqlCnn::configDb($tmp_config);

        $db_i              = 0;
        $db_cnt            = count($bakdbs);
        $matched_fullnames = [];
        foreach ($bakdbs as $db_name)
        {
            // var_dump($db_name);
            $db_i++;
            $tablename_rows = $dbcnn->setText("SELECT `table_schema`,`table_name` 'tablename' FROM information_schema.Tables WHERE table_schema = '{$db_name}';")->setRetry(5, 120)->queryAll();
            // var_dump($tablename_rows);
            $tablerows_cnt = count($tablename_rows);
            $tablerows_i   = 0;
            foreach ($tablename_rows as $tablename_row)
            {
                $tablerows_i      += 1;
                $fullname         = "{$db_name}.{$tablename_row['tablename']}";
                $formatedfullname = "`{$db_name}`.`{$tablename_row['tablename']}`";
                echo "{$db_i}/{$db_cnt}  {$tablerows_i}/{$tablerows_cnt} {$fullname}\n";
                if (!strstr($fullname, 'game_analysis.user_lottery_records'))
                {
                    // continue;
                }
                $is_match = false;
                foreach ($back_fullnames as $back_fullname)
                {
                    //  var_dump($back_fullname);
                    $ar       = [];
                    $is_match = preg_match("/^{$back_fullname}$/i", $fullname, $ar);
                    //var_dump([$ar,$is_match]);
                    if ($is_match)
                    {
                        $matched_fullnames[] = $formatedfullname;
                    }
                }

            }
        }
        //var_dump($matched_fullnames,array_unique($matched_fullnames));

        $tables_cnt         = count($matched_fullnames);
        $tables_i           = 0;
        $total_rows_cnt     = 0;
        $total_got_rows_cnt = 0;

        if ($this->yesOrNoConfirm("是否统计行数", ['yes', 'y', ''], "开始导出", "跳过导出"))
        {
            $this->printer->newTabEcho('start_count_tables', '开始-统计被匹配表信息');

            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i       += 1;
                $table_rows_cnt = intval($dbcnn->setText("select count(id) from {$matched_fullname}")->queryScalar());
                $total_rows_cnt += $table_rows_cnt;
                $this->printer->tabEcho("{$tables_i}/{$tables_cnt} :{$matched_fullname}  rows cnt:{$table_rows_cnt} ");
            }
            $this->printer->endTabEcho('start_count_tables', '结束-统计被匹配表信息');
        }


        $this->printer->newTabEcho('start_export_tables', '准备导出');
        if ($this->yesOrNoConfirm("是否导出数据，注意选择，\n!!!!!!!!!!小心覆盖已有文件!!!!!", ['export'], "开始导出", "跳过导出"))
        {
            $is_cover = $this->yesOrNoConfirm("\n!!!!!!!!!!默认是断点续传，输入[recover]覆盖文件!!!!!!!!\n", ['cover'], "开始覆盖", "断点续传");
            $tables_i = 0;
            $size     = 10000;
            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i += 1;
                $this->printer->newTabEcho('start_export_table', "{$tables_i}/{$tables_cnt} :{$matched_fullname}");

                $min_id         = $dbcnn->setText("select min(id) from {$matched_fullname}")->queryScalar();
                $table_rows_cnt = $dbcnn->setText("select count(id) from {$matched_fullname}")->queryScalar();
                $bakfilename    = "{$bakupdir}/{$matched_fullname}.json_strs";
                if (!file_exists($bakfilename))
                {
                    file_put_contents($bakfilename, '');
                }
                $this->printer->tabEcho("{$tables_i}/{$tables_cnt} :{$matched_fullname}   min_id:{$min_id} rows cnt:{$table_rows_cnt} {$bakfilename}");

                if ($is_cover)
                {
                    file_put_contents($bakfilename, '');
                }
                else
                {
                    $tmp_file = new File($bakfilename);
                    $line_num = 0;
                    $tmp_ar   = $tmp_file->getHeaderAndTailStrs();
                    if (count($tmp_ar) === 2)
                    {
                        $line_num = $tmp_file->getLinesNumber() - 1;
                        list($header_json, $tail_json) = $tmp_ar;
                        $header_row = json_decode($header_json, true);
                        $tail_row   = json_decode($tail_json, true);
                        if (isset($header_row['id']) && isset($tail_row['id']))
                        {
                            $this->printer->tabEcho("从文件获 {$line_num}行,起始id:{$header_row['id']}/{$min_id} 结束id:{$tail_row['id']}  ");
                            // $this->printer->tabEcho($header_json);
                            //  $this->printer->tabEcho($tail_json);
                            $min_id             = $tail_row['id'];
                            $total_got_rows_cnt += $line_num;
                        }
                        else
                        {
                            $this->printer->endTabEcho('verify_exported_file', '数据找不到id,异常！！！！！!!!!!!!!!!');
                            file_put_contents($bakfilename, '');
                        }
                    }
                    else
                    {
                        $this->printer->endTabEcho('verify_exported_file', '备份文件是空的，异常！！！！！!!!!!!!!!!');
                        file_put_contents($bakfilename, '');
                    }
                }

                $loop_times = 0;
                $last_time  = time();
                while (true)
                {
                    $curr_rows          = $dbcnn->setText("select * from {$matched_fullname} where id>={$min_id} order by id asc limit {$size}")->setRetry(5, 120)->queryAll();
                    $curr_rows_cnt      = count($curr_rows);
                    $total_got_rows_cnt += $curr_rows_cnt;
                    $loop_times         += 1;

                    $now_time     = time();
                    $now_date     = date('Y-m-d H:i:s', $now_time);
                    $cost_seconds = $now_time - $last_time;
                    $this->printer->tabEcho("table loop:{$tables_i}/{$tables_cnt} :{$matched_fullname}   min_id:{$min_id} rows cnt:{$table_rows_cnt} rows loop times:{$loop_times}  current rows cnt:{$curr_rows_cnt}   [{$total_got_rows_cnt}/{$total_rows_cnt}]  [{$now_date}] cost:{$cost_seconds}");

                    foreach ($curr_rows as $curr_row)
                    {
                        file_put_contents($bakfilename, json_encode($curr_row) . "\n", FILE_APPEND);
                    }
                    if ($curr_rows_cnt < $size)
                    {
                        break;
                    }
                    else
                    {
                        $min_id = $curr_rows[$curr_rows_cnt - 1]['id'];
                    }
                    $last_time = $now_time;
                }
                $this->printer->endTabEcho('start_export_table', '导出结束');

            }
        }

        $this->printer->endTabEcho('start_export_tables', '导出结束');


        $file_rows_cnt = 0;
        $db_rows_cnt   = 0;
        $this->printer->newTabEcho('verify_exported_files', '开始-校验已经导出文件');
        // $matched_fullnames = ['`game_analysis`.`user_present_records`'];
        if ($this->yesOrNoConfirm("校验已经导出数据？", ['y', 'Y', 'Yes', 'yes', ''], "", ""))
        {
            $tables_i = 0;
            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i += 1;
                $this->printer->newTabEcho('verify_exported_file', "{$tables_i}/{$tables_cnt} :{$matched_fullname}");
                $bakfilename = "{$bakupdir}/{$matched_fullname}.json_strs";
                $tmp_file    = new File($bakfilename);
                $line_num    = 0;
                // var_dump($tmp_file->getLinesNumber(),$tmp_file->getHeaderAndTailStrs());
                $tmp_ar = $tmp_file->getHeaderAndTailStrs();
                if (count($tmp_ar) === 2)
                {
                    $line_num = $tmp_file->getLinesNumber() - 1;
                    list($header_json, $tail_json) = $tmp_ar;
                    $header_row = json_decode($header_json, true);
                    $tail_row   = json_decode($tail_json, true);
                    if (isset($header_row['id']) && isset($tail_row['id']))
                    {
                        // $this->printer->tabEcho($header_json);
                        //  $this->printer->tabEcho($tail_json);
                    }
                    else
                    {
                        $this->printer->endTabEcho('verify_exported_file', '数据找不到id,异常！！！！！!!!!!!!!!!');
                        continue;

                    }
                }
                else
                {
                    $this->printer->endTabEcho('verify_exported_file', '备份文件是空的，异常！！！！！!!!!!!!!!!');
                    continue;
                }

                $cnt          = $dbcnn->setText("select count(id) from {$matched_fullname} where id>={$header_row['id']} and id<={$tail_row['id']}")->queryScalar();
                $compare_flag = $line_num < $cnt ? 'ERROR' : 'OK';
                $this->printer->tabEcho("{$compare_flag} {$tables_i}/{$tables_cnt} :{$matched_fullname}   file cnt:{$line_num} db rows cnt:{$cnt} ");

                $this->printer->endTabEcho('verify_exported_file', '#');
                $file_rows_cnt += $line_num;
                $db_rows_cnt   += $cnt;

            }
        }

        $this->printer->endTabEcho('verify_exported_files', '结束-校验已经导出文件');


        $end_time = time() - 3600 * 24 * 181;
        $end_date = date('Y-m-m H:i:s', $end_time);
        $end_ymd  = intval(date('Ymd', $end_time));
        //set global max_allowed_packet =31457280
        $this->printer->newTabEcho('delete_db_with_exported_files', '开始-删除数据库老数据');
        // $matched_fullnames = ['`game_analysis`.`user_present_records`'];
        if ($this->yesOrNoConfirm("准备删除 [{$end_date}]以前的数据？", ['Yes', 'yes', 'delete'], "", ""))
        {
            $tables_i              = 0;
            $delete_total_rows_cnt = 0;
            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i             += 1;
                $delete_curr_rows_cnt = 0;

                $this->printer->newTabEcho('verify_exported_file', "{$tables_i}/{$tables_cnt} :{$matched_fullname}");
                $bakfilename = "{$bakupdir}/{$matched_fullname}.json_strs";


                $min_id             = $dbcnn->setText("select min(id) from {$matched_fullname} ")->queryScalar();
                $fp                 = fopen($bakfilename, 'r');
                $curr_file_line_num = 0;
                while (!feof($fp))
                {
                    fgets($fp);
                    $curr_file_line_num += 1;
                }
                fseek($fp, 0);
                $curr_line_num = 0;
                $ids           = [];
                while (!feof($fp))
                {
                    $json = trim(fgets($fp));
                    if (!empty($json))
                    {
                        $row = json_decode($json, true);
                        if (isset($row['id']) && isset($row['adate']))
                        {
                            if (intval($row['id'] < $min_id))
                            {
                                continue;
                            }
                            $row_ymd = intval(str_replace('-', '', substr($row['adate'], 0, 10)));
                            if ($row_ymd > $end_ymd)
                            {
                                $tmp_ids_cnt = count($ids);
                                if ($tmp_ids_cnt > 0)
                                {
                                    $row_date              = $row['adate'];
                                    $delete_total_rows_cnt += $tmp_ids_cnt;
                                    $delete_curr_rows_cnt  += $tmp_ids_cnt;
                                    $now_date              = date('Y-m-d H:i:s', time());
                                    $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}   db cnt:{$db_rows_cnt} totol delete:{$delete_total_rows_cnt}/{$file_rows_cnt}  current file :{$delete_curr_rows_cnt}/{$curr_file_line_num}  row date:{$row_date}/{$end_ymd} now:{$now_date}");
                                }
                                $this->printer->tabEcho($row);
                                $this->printer->tabEcho("{$row_ymd}>{$end_ymd} 结束");
                                break;
                            }
                            $ids[] = $row['id'];
                            if (count($ids) === 100)
                            {
                                $row_date              = $row['adate'];
                                $delete_total_rows_cnt += 100;
                                $delete_curr_rows_cnt  += 100;
                                $now_date              = date('Y-m-d H:i:s', time());
                                $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}   db cnt:{$db_rows_cnt} totol delete:{$delete_total_rows_cnt}/{$file_rows_cnt}  current file :{$delete_curr_rows_cnt}/{$curr_file_line_num}  row date:{$row_date}/{$end_ymd} now:{$now_date}");
                                $ids_str = join(',', $ids);
                                $r       = $dbcnn->setText("delete from {$matched_fullname} where id in ({$ids_str})")->execute();
                                $ids     = [];


                            }

                        }
                        else
                        {
                            break;
                        }
                    }
                    $curr_line_num += 1;

                }
                fclose($fp);


                $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}   file cnt:{$curr_file_line_num}  ");

                $this->printer->endTabEcho('verify_exported_file', '#');

            }
        }

        $this->printer->endTabEcho('delete_db_with_exported_files', '结束-删除数据库老数据');


    }


    public function export20230710()
    {
        $config = Sys::app()->getConfig();
        var_dump($config['mongo']['local']);
        $conf                        = $config['mongo']['local'];
        $uriOptions['authMechanism'] = 'SCRAM-SHA-1';
        $uriOptions['authSource']    = $conf['authSource'];
        $uriOptions['username']      = $conf['username'];
        $uriOptions['password']      = $conf['password'];


        $conn = new \MongoDB\Driver\Manager("mongodb://{$conf['host']}:{$conf['port']}/{$conf['dbname']}", $uriOptions);

        $param = [
            "find"       => 'game_role',
            'filter'     => [
                '_id' => 510501
            ],
            'projection' => ['role' => 1, 'item' => 1, 'invite' => 1, 'goodShop' => 1],
            'limit'      => 1
        ];
        $cmd   = new \MongoDB\Driver\Command($param);
        $res   = $conn->executeCommand($conf['dbname'], $cmd)->toArray();


        // var_dump($res[0]->role->vip);
        //var_dump($res[0]->rowl);

        $json = json_encode($res);
        echo "\n{$json}\n";

        // $cmd = new \MongoDB\Driver\Command([]);
        //     $conn->executeCommand($conf['dbname'], $cmd);

        $get_uid_sql               = "SELECT distinct uid FROM game_analysis.`user_daily_active` where adate>'2023-04-01 00:00:00' order by id asc limit 20000";
        $get_reg_sql               = "SELECT uid,reg_time,`name`,last_login FROM game_analysis.users where uid=:uid limit 1";
        $get_user_total_charge_sql = "SELECT total_recharge_amount FROM game_analysis.total_add_up_data WHERE uid=:uid limit 1;";
        $get_monthly_charge_sql    = "SELECT sum(pay_amount) as cnt,left(day,7) as m FROM game_analysis.user_daily_pay where  uid=:uid and adate>'2023-04-01 00:00:00' group by m;";

        $headers  = ['name' => '昵称', 'uid' => 'UID', 'regtime' => '注册实际', 'last_login_time' => '最后登录时间', 'vip' => 'VIP', 'total_charge' => '总充值', '2023-04' => '4月充值', '2023-05' => '5月充值', '2023-06' => '6月充值', '2023-07' => '7月充值'];
        $months   = ['2023-04' => 0, '2023-05' => 0, '2023-06' => 0, '2023-07' => 0];
        $uid_rows = Sys::app()->db('_sys_')->setText($get_uid_sql)->queryAll();
        $uids_cnt = count($uid_rows);
        $uids_i   = 0;
        $filename = '/var/www/web/public/export/tmp_shenhaixunbao.json';
        $filename = '/var/www/public/export/tmp_wanhaobuyu.json';

        file_put_contents($filename, "[\n" . json_encode($headers) . "\n");
        foreach ($uid_rows as $uid_row)
        {
            $uid  = intval($uid_row['uid']);
            $info = [
                'name'            => '#',
                'uid'             => $uid,
                'regtime'         => '',
                'last_login_time' => '',
                'vip'             => 0,
                'total_charge'    => 0,
            ];
            $info = array_merge($info, $months);

            $uids_i++;
            $userinfo             = Sys::app()->db('_sys_')->setText($get_reg_sql)->bindArray([':uid' => $uid])->queryRow();
            $info['total_charge'] = intval(Sys::app()->db('_sys_')->setText($get_user_total_charge_sql)->bindArray([':uid' => $uid])->queryScalar());
            $month_charge_rows    = Sys::app()->db('_sys_')->setText($get_monthly_charge_sql)->bindArray([':uid' => $uid])->queryAll();
            $param                = [
                "find"       => 'game_role',
                'filter'     => [
                    '_id' => $uid
                ],
                'projection' => ['role' => 1, 'item' => 1, 'invite' => 1, 'goodShop' => 1],
                'limit'      => 1
            ];
            $cmd                  = new \MongoDB\Driver\Command($param);
            $res                  = $conn->executeCommand($conf['dbname'], $cmd)->toArray();

            if (isset($res[0]->role->vip))
            {
                // var_dump($res[0]->role->vip);
                $info['vip'] = intval($res[0]->role->vip);
            }
            $info['name']            = $userinfo['name'];
            $info['regtime']         = $userinfo['reg_time'];
            $info['last_login_time'] = $userinfo['last_login'];

            foreach ($month_charge_rows as $month_charge_row)
            {
                $info[$month_charge_row['m']] = intval($month_charge_row['cnt']);
            }
            //  var_export($info);
            echo "\n{$uids_i}/{$uids_cnt}\n";
            //   die;
            file_put_contents($filename, ',' . json_encode($info) . "\n", FILE_APPEND);

        }
        file_put_contents($filename, "]", FILE_APPEND);


    }


    // /mnt/d/kingloneDoc/fastAdmin/hammer  tool/mysql backAndDelete --env=self --configfile=/mnt/d/kingloneDoc/fastAdmin/config/tmp/prod-shenhaixunbao.json --flag=20231027
    //{
    //  "ssh_cmds": [
    //    "ssh -fN -L33061:localhost:3306 -p22 root@xx.xx.xx.xx",
    //    "ssh -fN -L30001:localhost:30018 -p22 root@xx.xx.xx.xx"
    //  ],
    //  "ssh_password": "",
    //  "host": "127.0.0.1",
    //  "port": 33061,
    //  "username": "root",
    //  "password": "",
    //  "dbname": "game_analysis",
    //  "charset": "utf8mb4",
    //  "bakupdbs": {
    //    "db-admin": [
    //      "orders"
    //    ],
    //    "game_analysis": [
    //      "daily_budget_analysis",
    //      "user_lottery_records_(\\d{4})_(\\d{2})",
    //      "user_room_records_(\\d{4})_(\\d{2})"
    //    ]
    //  },
    //  "bakupdir": "/mnt/d/kingloneDoc/bak_sqls/prod_wanhaobuyu"
    //}
    public function backAndDelete()
    {
        //ssh -fN -L33061:localhost:3306 -p22 root@47.96.173.58
        // mysql -h127.0.0.1 --port=33061 -uroot -p'1qaz2wsx.mj'
        $config     = null;
        $configfile = $this->inputDataBox->getStringNotNull('configfile');
        $uq_flag    = $this->inputDataBox->getStringNotNull('flag');
        if (file_exists($configfile) && is_readable($configfile))
        {
            $config = json_decode(file_get_contents($configfile), true);
            if (!is_array($config))
            {
                Sys::app()->interruption()->setMsg("配置文件内容错误 {$configfile} ")->setCode('error')->setDebugData($config)->outError();
            }
        }
        else
        {
            Sys::app()->interruption()->setMsg("配置文件不存在 {$configfile} ")->setCode('error')->outError();

        }
        var_dump($config);
        $dbconfigbox  = new DataBox($config);
        $ssh_cmds     = $dbconfigbox->getArray('ssh_cmds');
        $ssh_password = $dbconfigbox->getString('ssh_password');
        foreach ($ssh_cmds as $ssh_cmd)
        {
            exec("ps aux|grep '{$ssh_cmd}'|grep -v grep", $ar);
            var_dump(['ps_res' => $ar]);
            $code_dir = __ROOT_DIR__;
            if (count($ar) === 0)
            {
                exec("/bin/bash {$code_dir}/console/shell/ssh_exec.sh '{$ssh_cmd}' '{$ssh_password}'", $ar2);
                var_dump(['exce_res' => $ar2]);
            }
        }


        $bakupdir = $dbconfigbox->getStringNotNull('bakupdir');
        if (!(file_exists($bakupdir) && is_writeable($bakupdir)))
        {
            Sys::app()->interruption()->setMsg("备份目录不存在或者不可写： {$bakupdir} ")->setCode('error')->outError();
        }

        $bakupdir = $bakupdir . '/' . $uq_flag;
        if (file_exists($bakupdir))
        {
            if (!is_dir($bakupdir))
            {
                Sys::app()->interruption()->setMsg("备份目录拼接之后是文件： {$bakupdir} ")->setCode('error')->outError();
            }
        }
        else
        {
            mkdir($bakupdir);
        }

        $bakdbs         = [];
        $back_fullnames = [];
        $bakMap         = $dbconfigbox->getArrayNotNull('bakupdbs');


        $end_time           = time() - 3600 * 24 * 181;
        $end_date           = date('Y-m-m H:i:s', $end_time);
        $end_ymd            = intval(date('Ymd', $end_time));//结束日期
        $table_count_map    = [];                            //涉及表的行数统计，最差情况全要备份删除
        $table_filename_map = [];                            //本分文件名

        foreach ($bakMap as $dbname => $tablenames)
        {
            $bakdbs[] = $dbname;
            foreach ($tablenames as $tablename)
            {
                $back_fullnames[] = "{$dbname}.{$tablename}";
            }
        }


        $tmp_config = [
            'host'       => $dbconfigbox->getStringNotNull('host'),
            'port'       => $dbconfigbox->getIntNotNull('port'),
            'dbname'     => $dbconfigbox->getStringNotNull('dbname'),
            'username'   => $dbconfigbox->getStringNotNull('username'),
            'password'   => $dbconfigbox->getStringNotNull('password'),
            'charset'    => $dbconfigbox->getStringNotNull('charset'),
            'readOnly'   => true,
            'attributes' => [
                \PDO::ATTR_TIMEOUT => 600
            ]
        ];
        //Sys::app()->addLog($dev_cfg);
        $dbcnn = MysqlCnn::configDb($tmp_config);


        $db_i              = 0;
        $db_cnt            = count($bakdbs);
        $matched_fullnames = [];
        foreach ($bakdbs as $db_name)
        {
            // var_dump($db_name);
            $db_i++;
            $tablename_rows = $dbcnn->setText("SELECT `table_schema`,`table_name` 'tablename' FROM information_schema.Tables WHERE table_schema = '{$db_name}';")->setRetry(5, 120)->queryAll();
            // var_dump($tablename_rows);
            $tablerows_cnt = count($tablename_rows);
            $tablerows_i   = 0;
            foreach ($tablename_rows as $tablename_row)
            {
                $tablerows_i      += 1;
                $fullname         = "{$db_name}.{$tablename_row['tablename']}";
                $formatedfullname = "`{$db_name}`.`{$tablename_row['tablename']}`";
                echo "{$db_i}/{$db_cnt}  {$tablerows_i}/{$tablerows_cnt} {$fullname}\n";
                if (!strstr($fullname, 'game_analysis.user_lottery_records'))
                {
                    // continue;
                }
                $is_match = false;
                foreach ($back_fullnames as $back_fullname)
                {
                    //  var_dump($back_fullname);
                    $ar       = [];
                    $is_match = preg_match("/^{$back_fullname}$/i", $fullname, $ar);
                    //var_dump([$ar,$is_match]);
                    if ($is_match)
                    {
                        $matched_fullnames[] = $formatedfullname;

                        $table_filename_map[$formatedfullname] = "{$bakupdir}/{$formatedfullname}.{$end_ymd}.json_strs";
                    }
                }

            }
        }
        var_dump($matched_fullnames, array_unique($matched_fullnames), $table_filename_map);
        if (1)
        {
            // die;
        }


        $tables_cnt         = count($matched_fullnames);
        $tables_i           = 0;
        $total_rows_cnt     = 0;
        $total_got_rows_cnt = 0;
        $file_rows_cnt      = 0;


        if ($this->yesOrNoConfirm("是否统计行数", ['yes', 'y', ''], "开始导出", "跳过导出"))
        {
            $this->printer->newTabEcho('start_count_tables', '开始-统计被匹配表信息');

            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i       += 1;
                $table_rows_cnt = intval($dbcnn->setText("select count(id) from {$matched_fullname}")->queryScalar());
                $total_rows_cnt += $table_rows_cnt;
                $this->printer->tabEcho("{$tables_i}/{$tables_cnt} :{$matched_fullname}  rows cnt:{$table_rows_cnt} ");
                $table_count_map[$matched_fullname] = $table_rows_cnt;
            }
            $this->printer->endTabEcho('start_count_tables', '结束-统计被匹配表信息');
        }


        $this->printer->newTabEcho('start_export_tables', '准备导出');
        if ($this->yesOrNoConfirm("是否导出数据，注意选择，\n!!!!!!!!!!小心覆盖已有文件!!!!!", ['export'], "开始导出", "跳过导出"))
        {
            $is_cover = $this->yesOrNoConfirm("\n!!!!!!!!!!默认是断点续传，输入[recover]覆盖文件!!!!!!!!\n", ['cover'], "开始覆盖", "断点续传");
            $tables_i = 0;
            $size     = 10000;
            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i += 1;
                $this->printer->newTabEcho('start_export_table', "{$tables_i}/{$tables_cnt} :{$matched_fullname}");

                $min_id                = intval($dbcnn->setText("select min(id) from {$matched_fullname}")->queryScalar());
                $curr_table_rows_count = isset($table_count_map[$matched_fullname]) ? $table_count_map[$matched_fullname] : $dbcnn->setText("select count(id) from {$matched_fullname}")->queryScalar();
                $bakfilename           = $table_filename_map[$matched_fullname];
                if (!file_exists($bakfilename))
                {
                    file_put_contents($bakfilename, '');
                }
                $this->printer->tabEcho("{$tables_i}/{$tables_cnt} :{$matched_fullname}   min_id:{$min_id} rows cnt:{$curr_table_rows_count} {$bakfilename}");

                if ($is_cover)
                {
                    file_put_contents($bakfilename, '');
                }
                else
                {
                    $tmp_file = new File($bakfilename);
                    $line_num = 0;
                    $tmp_ar   = $tmp_file->getHeaderAndTailStrs();
                    if (count($tmp_ar) === 2)
                    {
                        $line_num = $tmp_file->getLinesNumber() - 1;
                        list($header_json, $tail_json) = $tmp_ar;
                        $header_row = json_decode($header_json, true);
                        $tail_row   = json_decode($tail_json, true);
                        if (isset($header_row['id']) && isset($tail_row['id']))
                        {
                            $this->printer->tabEcho("从文件获 {$line_num}行,起始id:{$header_row['id']}/{$min_id} 结束id:{$tail_row['id']}  ");
                            // $this->printer->tabEcho($header_json);
                            //  $this->printer->tabEcho($tail_json);
                            $min_id             = $tail_row['id'];
                            $total_got_rows_cnt += $line_num;
                        }
                        else
                        {
                            $this->printer->endTabEcho('verify_exported_file', '数据找不到id,异常！！！！！!!!!!!!!!!');
                            file_put_contents($bakfilename, '');
                        }
                    }
                    else
                    {
                        $this->printer->endTabEcho('verify_exported_file', '备份文件是空的，异常！！！！！!!!!!!!!!!');
                        file_put_contents($bakfilename, '');
                    }
                }

                $loop_times       = 0;
                $last_time        = time();
                $row_ymd          = 100000;
                $cur_got_rows_cnt = 0;
                while (true)
                {
                    $tmp_sql = "select * from {$matched_fullname} where id>={$min_id} order by id asc limit {$size}";
                    //$this->printer->tabEcho($tmp_sql);
                    $curr_rows          = $dbcnn->setText($tmp_sql)->setRetry(5, 120)->queryAll();
                    $curr_rows_cnt      = count($curr_rows);
                    $total_got_rows_cnt += $curr_rows_cnt;
                    $cur_got_rows_cnt   += $curr_rows_cnt;
                    $loop_times         += 1;

                    $now_time     = time();
                    $now_date     = date('d H:i:s', $now_time);
                    $cost_seconds = $now_time - $last_time;
                    $this->printer->tabEcho("tables:{$tables_i}/{$tables_cnt} {$matched_fullname}   min id:{$min_id}  rows loop times:{$loop_times}   rows cnt[ curr:{$cur_got_rows_cnt}/{$curr_table_rows_count} total:{$total_got_rows_cnt}/{$total_rows_cnt} ]  {$row_ymd}<{$end_ymd} [{$now_date}] cost:{$cost_seconds}");

                    foreach ($curr_rows as $curr_row)
                    {
                        if (!isset($curr_row['adate']))
                        {
                            var_dump($curr_row, '问题数据');
                            die;
                        }
                        $row_ymd = intval(str_replace('-', '', substr($curr_row['adate'], 0, 10)));
                        if ($row_ymd === 0)
                        {
                            var_dump($curr_row, $row_ymd, '问题数据');
                            die;
                        }
                        if ($row_ymd > $end_ymd)
                        {
                            $this->printer->tabEcho("{$row_ymd}>{$end_ymd}  row_ymd>end_ymd foreach break;");
                            break;
                        }
                        file_put_contents($bakfilename, json_encode($curr_row) . "\n", FILE_APPEND);
                        $file_rows_cnt += 1;
                    }
                    if ($row_ymd > $end_ymd)
                    {
                        $this->printer->tabEcho("{$row_ymd}>{$end_ymd}  row_ymd>end_ymd  while break;");
                        break;
                    }
                    if ($curr_rows_cnt < $size)
                    {
                        break;
                    }
                    else
                    {
                        $min_id = $curr_rows[$curr_rows_cnt - 1]['id'];
                    }
                    $last_time = $now_time;
                }
                $this->printer->endTabEcho('start_export_table', '导出结束');

            }
        }
        else
        {
            die("\n 不备份 不继续\n");
        }

        $this->printer->endTabEcho('start_export_tables', '导出结束');


        //set global max_allowed_packet =31457280
        $this->printer->newTabEcho('delete_db_with_exported_files', '开始-删除数据库老数据');
        // $matched_fullnames = ['`game_analysis`.`user_present_records`'];
        if ($this->yesOrNoConfirm("准备从导出文件中删除 [{$end_date}]以前的数据？", ['Yes', 'yes', 'delete'], "继续删除", "停止"))
        {
            $tables_i              = 0;
            $delete_total_rows_cnt = 0;
            foreach ($matched_fullnames as $matched_fullname)
            {
                $tables_i             += 1;
                $delete_curr_rows_cnt = 0;

                $this->printer->newTabEcho('verify_exported_file', "{$tables_i}/{$tables_cnt} :{$matched_fullname}");
                $bakfilename = $table_filename_map[$matched_fullname];

                $fp                       = fopen($bakfilename, 'r');
                $curr_file_line_num_total = 0;
                while (!feof($fp))
                {
                    fgets($fp);
                    $curr_file_line_num_total += 1;
                }
                fseek($fp, 0);
                $curr_line_num = 0;
                $ids           = [];
                while (!feof($fp))
                {
                    $json = trim(fgets($fp));
                    if (!empty($json))
                    {
                        $row = json_decode($json, true);
                        if (isset($row['id']) && isset($row['adate']))
                        {
                            $row_ymd = intval(str_replace('-', '', substr($row['adate'], 0, 10)));
                            if ($row_ymd > $end_ymd)
                            {
                                $tmp_ids_cnt = count($ids);
                                if ($tmp_ids_cnt > 0)
                                {
                                    $row_date              = $row['adate'];
                                    $delete_total_rows_cnt += $tmp_ids_cnt;
                                    $delete_curr_rows_cnt  += $tmp_ids_cnt;
                                    $now_date              = date('Y-m-d H:i:s', time());
                                    $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}  totol delete:{$delete_total_rows_cnt}/{$total_got_rows_cnt}  current file :{$delete_curr_rows_cnt}/{$curr_file_line_num_total}  row date:{$row_date}/{$end_ymd} now:{$now_date}");
                                }
                                $this->printer->tabEcho($row);
                                $this->printer->tabEcho("{$row_ymd}>{$end_ymd} 结束");
                                break;
                            }
                            $ids[] = $row['id'];
                            if (count($ids) === 100)
                            {
                                $row_date              = $row['adate'];
                                $delete_total_rows_cnt += 100;
                                $delete_curr_rows_cnt  += 100;
                                $now_date              = date('Y-m-d H:i:s', time());
                                $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}    totol delete:{$delete_total_rows_cnt}/{$file_rows_cnt}  current file :{$delete_curr_rows_cnt}/{$curr_file_line_num_total}  row date:{$row_date}/{$end_ymd} now:{$now_date}");
                                $ids_str = join(',', $ids);
                                $r       = $dbcnn->setText("delete from {$matched_fullname} where id in ({$ids_str})")->execute();
                                $ids     = [];
                            }
                        }
                        else
                        {
                            break;
                        }
                    }
                    $curr_line_num += 1;
                }
                $tmp_cnt = count($ids);
                if ($tmp_cnt > 0)
                {
                    $delete_total_rows_cnt += $tmp_cnt;
                    $delete_curr_rows_cnt  += $tmp_cnt;
                    $now_date              = date('Y-m-d H:i:s', time());
                    $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}    totol delete:{$delete_total_rows_cnt}/{$file_rows_cnt}  current file :{$delete_curr_rows_cnt}/{$curr_file_line_num_total}  {$end_ymd} now:{$now_date}");
                    $ids_str = join(',', $ids);
                    $r       = $dbcnn->setText("delete from {$matched_fullname} where id in ({$ids_str})")->execute();
                    $ids     = [];
                }
                fclose($fp);


                $this->printer->tabEcho(" {$tables_i}/{$tables_cnt} :{$matched_fullname}   file cnt:{$curr_file_line_num_total}  ");

                $this->printer->endTabEcho('verify_exported_file', '#');

            }
        }

        $this->printer->endTabEcho('delete_db_with_exported_files', '结束-删除数据库老数据');

        echo "\n";

    }


}
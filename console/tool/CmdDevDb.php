<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\tool;

use models\common\CmdBase;
use models\common\sys\Sys;

class CmdDevDb extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function init()
    {
        parent::init();
    }


    public function binlog()
    {
        $separator = 'KKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK';

        $users   = ['yangjl', 'zhangsq', 'xuyz'];
        $actions = ['CREATE TABLE', 'ALTER TABLE', 'DROP TABLE'];

        foreach ($users as $user_account)
        {
            $dst_file = Sys::app()->params['exportPath'] . "/sql_log_{$user_account}.sql";
            if ($this->inputDataBox->tryGetString('skip_scan') !== 'yes')
            {
                file_put_contents($dst_file, '');
                foreach ($actions as $action)
                {
                    $bin_log_create = "mysqlbinlog --base64-output=DECODE-ROWS  /var/lib/mysql/binlog.00000*|grep -A 30 -B 10 --group-separator '{$separator}' '{$action}'";
                    $cmd            = "docker exec -it mysql8_{$user_account} bash -c \"{$bin_log_create}\" >> {$dst_file} ";
                    echo "\n-- {$cmd}\n";
                    exec($cmd);
                }
            }

            echo "\n\n\n\n\n-- {$user_account} ------------------------------------------\n\n\n\n\n";
            $this->getSqlsFromFile($dst_file, $separator);

        }


    }

    public function getSqlsFromFile($dst_file, $separator)
    {
        $tablename_keyword = $this->inputDataBox->tryGetString('tablename_keyword');

        $strs = explode($separator, file_get_contents($dst_file));
        $sqls = [];

        $max_limit = 100000;
        $sql_i     = 0;
        foreach ($strs as $i => $str)
        {
            $time_flag = 'SET TIMESTAMP=';
            $end_flag  = '/*!*/;';
            if (!strstr($str, $time_flag) && !strstr($str, $end_flag))
            {
                var_dump($str);
                continue;
            }
            $lev2_strs   = explode("\n", $str);
            $last_time   = 0;
            $last_sql_ar = [];
            foreach ($lev2_strs as $lev2_i => $lev2_str)
            {
                $lev2_str = trim($lev2_str);
                if (strstr($lev2_str, $time_flag))
                {
                    $last_time   = preg_replace('/^SET TIMESTAMP=(\d+).*?$/i', '$1', $lev2_str);
                    $last_sql_ar = [];
                    continue;
                }
                if (preg_match('/^ALTER TABLE /i', $lev2_str) || preg_match('/^CREATE TABLE /i', $lev2_str))
                {
                    if (strlen($tablename_keyword) > 0 && !strstr($lev2_str, $tablename_keyword))
                    {
                        continue;
                    }
                    $last_sql_ar[] = $lev2_str;
                }
                else if (count($last_sql_ar))
                {
                    if ($lev2_str === '/*!*/;')
                    {
                        $sql_i++;
                        $sqls[$last_time * $max_limit + $sql_i] = [$sql_i, $last_time, join("\n", $last_sql_ar)];
                        $last_sql_ar                            = [];
                    }
                    else
                    {
                        $last_sql_ar[] = $lev2_str;
                    }
                }
            }
        }


        ksort($sqls);
        //var_dump($sqls);
        foreach ($sqls as $sql_info)
        {
            $date = date('Y-m-d H:i:s', $sql_info[1]);
            echo "\n-- {$date} sn:{$sql_info[0]}\n {$sql_info[2]};\n";
        }

        echo "\n";

    }
}
<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2023/6/12
 * Time: 09:01
 * 狂野钓鱼
 */

namespace console\system;

use models\common\CmdBase;
use models\common\db\Db;
use models\common\sys\Sys;

ini_set('memory_limit', '2024M');

class CmdInit extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function init()
    {
        parent::init();
    }

    /**
     * 0  配置 /path_to_code/config/env/default.php
     * 1. chmod +x  /path_to_code/hammer
     * 2. /path_to_code/hammer system/init run --env=default
     * @throws \Exception
     */
    public function run()
    {
        $logDir = Sys::app()->params['logDir'];
        $errors = [];
        if (!function_exists('mkdir'))
        {
            $errors[] = "需要开启 mkdir ";
        }


        if (!function_exists('chmod'))
        {
            $errors[] = "需要开启 chmod ";
        }


        if (!function_exists('exec'))
        {
            $errors[] = "需要开启 exec ";
        }
        if (!class_exists('PDO'))
        {
            $errors[] = "需要开启 PDO ";
        }
        if (!class_exists('mysqli'))
        {
            $errors[] = "需要开启 mysqli ";
        }

        if (count($errors))
        {
            echo join("\n", $errors);
            die("\n");
        }
        $root_dir  = __ROOT_DIR__;
        $init_dirs = [
            $logDir . '/web',
            $logDir . '/cmd',
            $logDir . '/cli',
            $logDir . '/file/image',
            $root_dir . '/static/upload'
        ];
        foreach ($init_dirs as $init_dir)
        {
            $dst_file = "{$init_dir}";
            if (file_exists($dst_file))
            {
                chmod($dst_file, 0777);
                echo "\n {$dst_file} 文件已经存在，修改权限 \n";
            }
            else
            {
                mkdir($dst_file, 0777, true);
                chmod($dst_file, 0777);
                echo "\n {$dst_file} 创建文件 \n";
            }
        }

        $init_db_sql = "{$root_dir}/config/file/initdb.sql";
        if (!is_file($init_db_sql) || !is_readable($init_db_sql))
        {
            die("\nERROR:数据库初始文件找不到了或者不可读：{$init_db_sql} \n");
        }
        $sql  = file_get_contents($init_db_sql);
        $rand = strval(rand(10000, 100000));
        fwrite(STDOUT, "----------------------------------\n下面操作会覆盖数据，请输入中括号内数字 以继续[{$rand}]:");
        $code = trim(fgets(STDIN));
        echo "\n----------------------------------\n";
        if ($rand !== $code)
        {
            die("\n验证码不正确，已经停止\n");
        }
        else
        {
            echo "\n准备覆盖数据\n";
            sleep(5);
        }

        echo "\n数据开始初始化:\n";
        $cnn    = Sys::app()->db('_sys_');
        $cfg    = $cnn->cfg;
        $mysqli = new \mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['dbname'], $cfg['port']);
        $mysqli->multi_query($sql);
        echo "\nEND\n";

    }


}
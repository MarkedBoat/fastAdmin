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
use modules\_dp\v1\model\Admin;

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
     * 先确认有没有  /usr/local/bin/php  如果没有  sudo ln -s path_to_php /usr/local/bin/php
     * 0  配置mysql  /path_to_code/config/env/default.php
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
        $rand = strval(rand(10000, 100000));
        fwrite(STDOUT, "----------------------------------\n下面操作会覆盖数据，请输入中括号内数字 以继续[{$rand}]:");
        $code = trim(fgets(STDIN));
        echo "\n----------------------------------\n";
        if ($rand !== $code)
        {
            //   die("\n验证码不正确，已经停止\n");

            echo "\n10秒后，验证码不正确，进入设置账号密码\n";
            for ($i = 11; $i > 0; $i--)
            {
                echo "{$i}s后,验证码不正确，进入设置账号密码\n";
                sleep(1);
            }
        }
        else
        {
            echo "\n5秒后 覆盖数据\n";
            //sleep(5);
            for ($i = 1; $i < 6; $i++)
            {
                echo "{$i}s\n";
                sleep(1);
            }

            echo "\n数据开始初始化:\n";
            $cnn = Sys::app()->db('_sys_');
            $cfg = $cnn->cfg;

            // $sql  = file_get_contents($init_db_sql);
            //$mysqli = new \mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['dbname'], $cfg['port']);
            //$mysqli->multi_query($sql);

            $file = fopen($init_db_sql, "r");

            $queryCount = 0;
            $query      = '';
            while (!feof($file))
            {
                $line = fgets($file);
                if (substr($line, 0, 2) == '--' OR trim($line) == '')
                {
                    continue;
                }

                $query .= $line;
                $query = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_general_ci', $query);
                if (substr(trim($query), -1) == ';')
                {
                    $igweze_prep = $cnn->prepare($query);
                    if (!($igweze_prep->execute()))
                    {
                        var_dump($query);
                        print_r($cnn->errorInfo());
                        die;
                    }
                    echo "{$queryCount}:{$query}\n";
                    $query = '';
                    $queryCount++;
                }
            }

            fclose($file);


            echo "\nEND\n";
        }

        $account = 'superadmin';
        fwrite(STDOUT, "----------------------------------\n设置 [ {$account} ] 密码:");
        $psw = trim(fgets(STDIN));
        if (empty($psw))
        {
            echo "\n空密码，不设置\n";
        }
        else
        {
            $md5            = md5($psw);
            $user           = Admin::model()->findOneByWhere(['real_name' => $account]);
            $compute_str    = substr(md5($md5 . $user->salt . $user->create_time), 2);
            $user->password = $compute_str;
            $user->update();
            echo "\naccount:{$account}\npassword:{$psw}\n\n";
        }

    }


}
<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace models\common;

use models\common\param\DataBox;
use models\common\param\Params;
use models\common\sys\Sys;

//更新计划凭借代码方法 sh ~/kinglone.sh "/data/git-webroot/api-htdocs/CLI/" "origin/master"
// 简易计划任务操作  sudo chmod +x /data/git-webroot/api-htdocs/CLI/itfc/hammer cmd:/data/git-webroot/api-htdocs/CLI/itfc/hammer bftv.user.service.renew starter --service=kids --planId=renewKidsMember --env=prod --deadLineTs=1550222461
class CmdBase
{
    public static $logDir       = '';
    public static $env          = '';
    protected     $inputDataBox = null;
    private       $__planId     = '';
    protected     $deadLineTs   = 0;


    public function __construct($param = [])
    {
        $this->inputDataBox = new DataBox($param);
        // static::init($param);
        $time_limit       = $this->inputDataBox->tryGetInt('timeLimit');
        $this->deadLineTs = $this->inputDataBox->tryGetInt('deadLineTs');
        if ($this->deadLineTs === 0 && $time_limit)
        {
            $this->deadLineTs = time() + $time_limit;
        }
        $this->init();

    }

    public function init()
    {
        // $this->params = new Params($param);
        // $this->__apiName = $this->args->tryGetString('method');
    }


    public function run()
    {

        // sleep(1);
    }


    public static function compareDate($date, $date_)
    {
        if ($date_ === '*')
        {
            return true;
        }
        else
        {
            if (substr($date_, 0, 2) === '*/')
            {
                return $date % intval(substr($date_, 2)) === 0 ? true : false;
            }
            else
            {
                $dateInt = intval($date_);
                return $date === $dateInt ? true : false;
            }
        }
        //return $date_ === '*' || $date == intval($date_) || strstr($date_, '*/') ? ($date % intval(str_replace('*/', '', $date_)) === 0 ? true : false) : false;
    }

    public static function getPlanRunning($planId)
    {
        // $planId .= '_';
        $cmdStr = "ps aux|grep php|grep 'planId=$planId'|grep -v grep|grep -v timeout|grep -v '>'";
        exec($cmdStr, $ar);
        return $ar;
    }

    public static function killTimeout($cmdLines, $nowTs)
    {
        // echo join("\n", $cmdLines);
        foreach ($cmdLines as $str)
        {
            $ar       = explode(' ', $str);
            $cmds     = [];
            $deadline = 0;
            foreach ($ar as $e)
            {
                if (strlen(trim($e)))
                    $cmds[] = trim($e);
                if (strstr($e, '--deadLineTs='))
                    $deadline = intval(str_replace('--deadLineTs=', '', $e));
            };
            $pid = intval($cmds[1]);
            $d1  = date('Y-m-d H:i:s', $deadline);
            $d2  = date('Y-m-d H:i:s', $nowTs);
            if ($d1 < $d2)
            {
                $cmd = "kill {$pid}";
                echo "\nKILLED:{$pid}\t#{$d2}>{$d1}\t#$str";
                exec($cmd);
            }
        }
    }

    /**
     * 获取当前命令状态
     * @return bool|string
     */
    public function getCurrentStatus()
    {
        return Sys::app()->redis('default')->get('cmd_current_status_' . $this->__planId);
    }

    /**
     *
     * @return bool
     */
    public function isCmdShutdown()
    {
        $shutdownStatus = Sys::app()->redis('default')->get('cmd_is_shutdown_' . $this->__planId);
        return $shutdownStatus === 'yes' ? true : false;
    }


    /**
     * 获取倒计时还有多少秒  --deadLineTs= 优先  其次 --timeLimit= ，不然会得出 负数结果
     * @return int
     */
    public function getCountdownSeconds()
    {
        return $this->deadLineTs - time();
    }

}
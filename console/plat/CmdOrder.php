<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\plat;

use models\common\CmdBase;
use models\common\sys\Sys;

class CmdOrder extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function notify()
    {
        $key = 'plat_order_notify';


        while (true)
        {

        }

    }


}
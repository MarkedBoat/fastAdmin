<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\bee_invasion;

use models\common\CmdBase;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\Printer;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\rank\RankTopDao;
use modules\bee_invasion\v1\dao\game\rank\RoleScoreDataDao;
use modules\bee_invasion\v1\dao\game\rank\RoleStageScoreDao;
use modules\bee_invasion\v1\dao\user\UserFakeDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\economy\plat\Partner;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserObjectHis;

class CmdCache extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function clear()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 清理缓存  start\n";
        $keys = explode(',', $this->inputDataBox->getStringNotNull('keys'));
        if (in_array('*', $keys))
        {
            $keys = ['*'];
        }
        $redis_keys = [];
        foreach ($keys as $key_pattern)
        {
            if ($key_pattern === 'api')
            {
                $all_keys = Sys::app()->redis('cache')->keys('*');
                foreach ($all_keys as $tmp_key)
                {
                    if (strlen($tmp_key) === 32 && !strstr($tmp_key, '_'))
                    {
                        $redis_keys[] = $tmp_key;
                    }
                }
            }
            else
            {
                $redis_keys = Sys::app()->redis('cache')->keys($key_pattern);
            }
        }
        var_dump($redis_keys);
        Sys::app()->redis('cache')->del($redis_keys);
    }


}
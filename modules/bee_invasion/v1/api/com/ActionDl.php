<?php

namespace modules\bee_invasion\v1\api\com;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\api\open\OpenBaseAction;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;


class ActionDl extends ActionBase
{
    public function run()
    {

        $url = 'https://bee-invasion.oss-cn-hangzhou.aliyuncs.com/sdk/app-aqyh-1-2.apk';
        ob_start();
        //$contents = ob_get_contents();
        ob_end_clean();
        header('Location:' . $url);

        die;
    }
}
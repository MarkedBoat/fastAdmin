<?php

namespace modules\bee_invasion\v1\api\game\role;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;


class ActionEgHtml extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }


    public function run()
    {
        $url = 'https://qr.alipay.com/bax097580uuey9qjvipc25ed';
        die("<html><head><title>测试</title></head><body> <script> 
window.location.href='alipays://platformapi/startapp?saId=10000007&qrcode={$url}'
</script></body></html>");
    }
}
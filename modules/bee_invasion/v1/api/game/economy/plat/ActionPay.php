<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

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


class ActionPay extends ActionBase
{
    public function run()
    {


        Sys::app()->setDebug(true);
        $order_info = $this->inputDataBox->getStringNotNull('order_info');
        $alipay_app = $this->inputDataBox->getStringNotNull('alipay_app');
        return $this->renderTpls(['/web/pay/paying.html'], ['order_info' => $order_info, 'alipay_app' => $alipay_app]);
    }
}
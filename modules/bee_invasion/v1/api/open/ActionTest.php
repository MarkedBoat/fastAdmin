<?php

namespace modules\bee_invasion\v1\api\open;

use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\user\UserFakeDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionTest extends ActionBase
{

    public function run()
    {
        $s              = $this->inputDataBox->getStringNotNull('s');
        $user           = User::model()->findByPk(1);
        $user->nickname = $s;
        $user->update();
        $this->dispatcher->setOutType(Api::outTypeText);
        $key = '<rlvXL^B3YM~u2%|7]m9$IG_o)ADFNd:j*"J5zh&';
        var_dump(md5(sha1('e10adc3949ba59abbe56e057f20f883e') . $key));
        var_dump(pow(10, 0));

        \models\Api::$hasOutput = true;

    }
}
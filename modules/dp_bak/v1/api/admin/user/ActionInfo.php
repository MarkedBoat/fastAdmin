<?php

namespace modules\dp\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\dp\v1\api\admin\AdminBaseAction;

class ActionInfo extends AdminBaseAction
{


    public function run()
    {
        return $this->user->getOpenInfo();
    }


}
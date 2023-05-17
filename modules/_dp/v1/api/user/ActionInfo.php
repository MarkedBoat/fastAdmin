<?php

namespace modules\_dp\v1\api\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\_dp\v1\api\AdminBaseAction;

class ActionInfo extends AdminBaseAction
{
    protected $isAllUserAccess = true;

    public function run()
    {
        return $this->user->getOpenInfo();
    }


}
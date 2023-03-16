<?php

namespace modules\_dp\v1\api\index;

use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\_dp\v1\dao\AdminTokenDao;
use modules\_dp\v1\model\Admin;
use modules\dp\v1\api\admin\AdminBaseAction;

class ActionRender_index extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeHtml);
        return $this->renderTpls(['/modules/_dp/v1/view/index.html'], []);

    }
}
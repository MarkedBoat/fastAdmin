<?php

namespace modules\_dp\v1\api\user;

use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\_dp\v1\dao\AdminTokenDao;
use modules\_dp\v1\model\Admin;

class ActionRender_login extends ActionBase
{
    public function run()
    {
        session_start();
        unset($_SESSION['utk']);
        $this->dispatcher->setOutType(Api::outTypeHtml);
        $pub_key = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pub.key');
        return $this->renderTpls(['/modules/_dp/v1/view/admin/login.html'], ['publicKey' => $pub_key]);

    }
}
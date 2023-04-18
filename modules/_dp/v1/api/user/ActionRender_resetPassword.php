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

class ActionRender_resetPassword extends ActionBase
{
    public function init()
    {
        $this->setOutputHtml();
        parent::init();
    }

    public function run()
    {
        $pub_key = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pub.key');
        return $this->renderTpls(['/modules/_dp/v1/view/admin/resetpsw.html'], ['publicKey' => $pub_key]);
    }
}
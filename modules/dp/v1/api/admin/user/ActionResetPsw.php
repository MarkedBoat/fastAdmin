<?php

namespace modules\dp\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\AdminTokenDao;
use modules\dp\v1\model\admin\Admin;

class ActionResetPsw extends AdminBaseAction
{


    public function run()
    {
        $psw1 = $this->inputDataBox->getStringNotNull('psw1');
        $psw2 = $this->inputDataBox->getStringNotNull('psw2');

        $pri_key = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pri.key');
        $md5_1   = RSA::de($pri_key, $psw1);
        if (empty($md5_1))
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], '密码异常', $md5_1);
        }

        $md5_2 = RSA::de($pri_key, $psw2);
        if (empty($md5_2))
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], '密码异常', $md5_2);
        }


        if ($md5_1 !== $md5_2)
        {
            throw new AdvError(AdvError::request_param_verify_fail, '两次密码不一样');
        }


        $compute_str = substr(md5($md5_1 . $this->user->salt . $this->user->create_time), 2);


        $this->user->password    = $compute_str;
        $this->user->update_time = date('Y-m-d H:i:s', time());

        return ['res' => $this->user->update()];
    }



}
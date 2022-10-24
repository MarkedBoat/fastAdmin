<?php

namespace modules\bee_invasion\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionResetPsw extends AdminBaseAction
{


    public function run()
    {
        $psw1 = $this->inputDataBox->getStringNotNull('psw1');
        $psw2 = $this->inputDataBox->getStringNotNull('psw2');
        $password = $this->getPwd($psw1);
        $password2= $this->getPwd($psw2);
        if ($password !== $password2)
        {
            throw new AdvError(AdvError::request_param_verify_fail, '两次密码不一样');
        }



        $key    = '<rlvXL^B3YM~u2%|7]m9$IG_o)ADFNd:j*"J5zh&';
        $db_psw = md5(sha1($password) . $key);

        $this->user->password = $db_psw;
        return ['res' => $this->user->update()];
    }


    public function getPwd($pwd)
    {
        $prikey = $this->getRSAkey();
        //解密的是md5处理过得密码
        return RSA::de($prikey, $pwd);
    }

    public function getRSAkey()
    {
        //获取秘钥
        $prikey = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['AdminUserPwdKey']['key']);
        if (!$prikey)
        {
            $prikey = file_get_contents('data/upload/adminkl.pri');
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg']['AdminUserPwdKey']['key'], $prikey);
        }
        return $prikey;
    }
}
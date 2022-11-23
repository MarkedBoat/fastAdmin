<?php

namespace modules\_dp\v1\api\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\_dp\v1\dao\AdminTokenDao;
use modules\_dp\v1\model\Admin;

class ActionLogin extends ActionBase
{


    public function run()
    {
        $un      = $this->inputDataBox->getStringNotNull('username');
        $psw     = $this->inputDataBox->getStringNotNull('password');
        $pri_key = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pri.key');
        $md5     = RSA::de($pri_key, $psw);
        if (empty($md5))
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], '密码异常', $md5);
        }
        $user        = Admin::model()->findOneByWhere(['real_name' => $un]);
        $compute_str = substr(md5($md5 . $user->salt . $user->create_time), 2);
        if ($user->password !== $compute_str)
        {
            return $this->dispatcher->createInterruption(AdvError::data_info_unexpected['detail'], "密码错误", $compute_str);
        }
        $admin_token = AdminTokenDao::model()->findOneByWhere(['user_id' => $user->id], false);
        $is_add      = false;
        if (empty($admin_token))
        {
            $is_add               = true;
            $admin_token          = new AdminTokenDao();
            $admin_token->user_id = $user->id;
        }
        $admin_token->expires    = time() + 3600 * 24 * 7;
        $true_token              = "{$user->id}_{$admin_token->expires}";
        $pub_key                 = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pub.key');
        $rsa_token               = RSA::en($pub_key, $true_token);
        $admin_token->user_token = $true_token;
        if ($is_add)
        {
            $admin_token->insert();
        }
        else
        {
            $admin_token->update();
        }
        return ['token' => urlencode($rsa_token)];

    }
}
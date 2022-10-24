<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\user\ActionLogin;


class ActionFindpwd extends ActionBase
{


    public function run()
    {
        $result          = ['code' => 0,'status'=>400, 'msg' => '更新失败'];
        $data[':mobile'] = $this->inputDataBox->getStringNotNull('mobile');
        //校验手机号是否已注册
        $res = Sys::app()->db('dev')->setText("select * from bi_user where mobile=:mobile")->bindArray($data)->queryRow();
        if (!$res)
        {
            throw  new \Exception('该手机号未注册', 400);
        }
        //前后密码校验
        $password = $this->inputDataBox->getStringNotNull('password');
        $login    = new ActionLogin();
        $password = $login->getPwd($password);

        //校验验证码
        $smscode   = $this->inputDataBox->getStringNotNull('smscode');
        $rediscode = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data[':mobile']);
        if ($smscode != $rediscode)
        {
            throw  new \Exception('验证码不正确或失效，请重新获取', 400);
        }
        $update            = 'password = :password';
        $data[':password'] = $login->think_ucenter_md5($password);

        if ($data[':password'] == $res['password'])
        {
            throw  new \Exception('新密码与旧密码一致，请重新设置', 400);
        }

        $res = Sys::app()->db('dev')->setText("update bi_user set " . $update . " where mobile=:mobile")->bindArray($data)->execute();
        if ($res)
        {
            $result = ['code' => 'ok', 'status' => '200', 'msg' => '更新成功'];
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);
        exit;
    }
}





<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\param\DataBox;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\api\game\user\ActionLogin;


class ActionInfo extends GameBaseAction
{


    public function run()
    {
        $type = $this->inputDataBox->getStringNotNull('type');
        if ($type == 1)
        {
            //updateInfo
            $res = $this->updateInfo();
        }
        elseif ($type == 2)
        {
            //find pwd
            $res = $this->userFindPwd();
        }
        else
        {
            //完善信息
            $data['nickname'] = $this->inputDataBox->getStringNotNull('nickname');
            $res              = $this->updateInfo();
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $res['data']['op_flag'] = $op_flag;
        }
        echo json_encode($res);exit;

    }

    //更新用户信息
    public function updateInfo()
    {
        $result = ['code' => 0, 'status'=>400,'msg' => '更新失败'];
        //根据手机号或token更新
        $update = '';
        if (isset($_REQUEST['nickname']) && $_REQUEST['nickname'])
        {
            $data['nickname'] = urldecode(trim($_REQUEST['nickname']));
            if (mb_strlen($data['nickname']) > 16)
            {
                throw  new \Exception('用户名长度不符合规范', 400);
            }
            //检验用户名是否重复
            $res = Sys::app()->db('dev')->setText("select id from bi_user where nickname=:nickname ORDER BY id desc limit 1")->bindArray(['nickname' => $data['nickname']])->queryRow();
            if ($res)
            {
                throw  new \Exception('用户名重复', 400);
            }

            $update = 'nickname = :nickname,';
        }
        if (isset($_REQUEST['password']) && $_REQUEST['password'])
        {
            //前后密码校验
            $password = $this->inputDataBox->getStringNotNull('password');
            //$pwd      = $this->inputDataBox->getStringNotNull('pwd');
            $login    = new ActionLogin();
            //解密的是md5处理过得密码
            $password = $login->getPwd($password);
            if (!$password)
            {
                throw  new \Exception('密码解密失败', 400);
            }
            $update            .= 'password = :password,';
            $data[':password'] = $login->think_ucenter_md5($password);
        }
        if (isset($_REQUEST['mobile']) && $_REQUEST['mobile'])
        {
            //校验手机号是否已注册
            $data1['mobile'] = $data['mobile'] = $_REQUEST['mobile'];
            $res             = Sys::app()->db('dev')->setText("select * from bi_user where mobile=:mobile")->bindArray($data1)->queryRow();
            if ($res)
            {
                throw  new \Exception('该手机号已注册', 400);
            }
            //校验验证码
            $smscode = $this->inputDataBox->getStringNotNull('smscode');
            if($smscode != Sys::app()->params['checkCode']['smscode']){
                $rediscode = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data['mobile']);
                if ($smscode != $rediscode)
                {
                    throw  new \Exception('验证码不正确或失效，请重新获取', 400);
                }
                Sys::app()->redis('cache')->delete(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data['mobile']);
            }
            $update .= 'mobile = :mobile,';
        }
        if (isset($_REQUEST['sex']) && $_REQUEST['sex'])
        {
            $data[':sex'] = $_REQUEST['sex'];
            $update       .= 'sex = :sex,';
        }
        if (isset($_REQUEST['email']) && $_REQUEST['email'])
        {
            $data[':email'] = $_REQUEST['email'];
            $update         .= 'email = :email,';
        }
        //绑定区域
        if(isset($_REQUEST['area_code']) && $_REQUEST['area_code']){
            $data1['user_id'] = $this->user->id;
            //判断是否已绑定数据
            $bindres = Sys::app()->db('dev')->setText("select * from bi_user_profile where user_id = :user_id and is_ok = 1")->bindArray($data1)->queryRow();
            if($bindres){
                throw  new \Exception('您已成功绑定区域，无需再次绑定', 400);
            }
            //判断是否已申请代理人
//            $areaApply = Sys::app()->db('dev')->setText("select * from bi_user_apply_bind_area where user_id = :user_id and is_ok = 1 and status != 3")->bindArray($data1)->queryRow();
//            if($areaApply){
//                throw  new \Exception('您正在申请成为代理人，暂不可绑定', 400);
//            }
            $data1['area_code'] = $_REQUEST['area_code'];
            //判断区域编码是否正确
            $areaArr = Sys::app()->db('dev')->setText("select * from bi_area where area_code = :area_code and area_level = 3")->bindArray(['area_code'=>$data1['area_code']])->queryRow();
            if (!$areaArr)
            {
                throw  new \Exception('区域编码不正确或失效，请重新获取', 400);
            }
            $arr                = Sys::app()->db('dev')->setText("insert into bi_user_profile (user_id,area_code) values (:user_id,:area_code) ")->bindArray($data1)->execute();
            if($arr){
                $result = ['code' => 'ok', 'status' => '200','msg'=>'更新成功'];
            }
        }
        if ($update)
        {
            $data[':id'] = $this->user->id;
            $update      = rtrim($update, ',');
            $res         = Sys::app()->db('dev')->setText("update bi_user set " . $update . " where id=:id")->bindArray($data)->execute();
            $result = ['code' => 'ok', 'status' => '200','msg'=>'更新成功'];
        }
        return $result;
    }

    //找回密码
    public function userFindPwd()
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
        $password   = $login->getPwd($password);

        //校验验证码
        $smscode   = $this->inputDataBox->getStringNotNull('smscode');
//        $redis     = new Redis();
//        $rediscode = $redis->get($data[':mobile']);
        $rediscode = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data[':mobile']);
        if ($smscode != $rediscode)
        {
            throw  new \Exception('验证码不正确或失效，请重新获取', 400);
        }
        Sys::app()->redis('cache')->del(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data[':mobile']);
        $update            = 'password = :password';
        $data[':password'] = $login->think_ucenter_md5($password);

        if ($data[':password'] == $res['password'])
        {
            throw  new \Exception('新密码与旧密码一致，请重新设置', 400);
        }

        $res = Sys::app()->db('dev')->setText("update bi_user set " . $update . " where mobile=:mobile")->bindArray($data)->execute();
        if ($res)
        {
            $result = ['code' => 'ok', 'status' => '200','msg'=>'更新成功'];
        }
        return $result;

    }

}
<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\model\user\User;

class ActionLoginNew extends ActionBase
{
    //新版登录注册
    public function run()
    {
        $res   = ['code' => 'ok','status' => 200,'msg' => '登录成功','data'=>[]];
        $data['is_ok'] = 1;
        $where         = 'is_ok = :is_ok';

        $data['mobile'] = $this->inputDataBox->getStringNotNull('mobile');
        $checkRedis = $this->checkRedis($data['mobile']);
        $smscode = $this->inputDataBox->getStringNotNull('smscode');
        if($smscode != Sys::app()->params['checkCode']['smscode']){
            $rediscode = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data['mobile']);
            if ($smscode != $rediscode)
            {
                throw  new \Exception('验证码不正确或失效，请重新获取', 400);
            }
            Sys::app()->redis('cache')->del(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data['mobile']);
        }
        $where .= " and mobile=:mobile";
        $arr = Sys::app()->db('dev')->setText("select * from bi_user where " . $where)->bindArray($data)->queryRow();

        if (!$arr)
        {
            //校验手机号是否已注册
            $userModel = new User();
            $userModel->mobile = $data['mobile'];
            $nickname = substr_replace($data['mobile'],'****',3,4);
            $userModel->nickname = $nickname;

            $userModel->insert(true);
            $userModel->generateOpenId();
            $uid = $userModel->id;
            if (!$uid)
            {
                throw  new \Exception('注册失败', 400);
            }

            $res['msg'] = '注册成功';
            $res['data']['user_id'] = $uid;
            $res['data']['flag'] = 'register';
            $res['data']['nickname'] = $nickname;
            //$res   = ['code' => 'ok','status' => 200, 'msg' => '注册成功', 'user_id' => $uid, 'token' => $token];
        }
        else
        {
            //判断是否封禁
            if($arr['is_ok'] == 2){
                throw  new \Exception('登录失败，该账号已被禁用！', 400);
            }
            $uid = $arr['id'];
            //增加token
            $res['data']['user_id'] = $arr['id'];
            $res['data']['flag'] = 'login';
            $res['data']['nickname'] = $arr['nickname'];
            //$res   = ['code' => 'ok','status' => 200, 'msg' => '登录成功', 'user_id' => $arr['id'], 'token' => $token];
        }
        $token = $this->getToken($uid);
        $res['data']['token'] = $token;
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $res['data']['op_flag'] = $op_flag;
        }
        echo json_encode($res);exit;
    }

    public function checkRedis($name){
        $cahceName = Sys::app()->params['cache_cfg']['UserLoginTimes']['key'] . $name;
        $num = Sys::app()->redis('cache')->get($cahceName);
        if($num){
            if($num >=2){
                throw  new \Exception('短时间登录频次过高，请稍后操作！', 400);
            }else{
                Sys::app()->redis('cache')->incrBy($cahceName,1);
            }
        }else{
            $num = Sys::app()->redis('cache')->set($cahceName,1,5);
        }
    }

    public function getRSAkey()
    {
        //获取秘钥
        $prikey = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserPwdKey']['key']);
        if (!$prikey)
        {
            $prikey = file_get_contents('data/upload/kl.pri');
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg']['UserPwdKey']['key'], $prikey);
        }
        return $prikey;
    }

    public function getRSAPkey()
    {
        //获取公钥
        $prikey = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserPwdKey']['key'] . '_pub');
        if (!$prikey)
        {
            $prikey = file_get_contents('data/upload/kl.pub');
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg']['UserPwdKey']['key'] . '_pub', $prikey);
        }
        return $prikey;
    }

    public function getPwd($pwd)
    {
        $prikey = $this->getRSAkey();
        //解密的是md5处理过得密码
        return RSA::de($prikey, $pwd);
    }

    //生成token的操作
    //type 1 注册 2 登录
    public function getToken($uid)
    {
        $res   = microtime(true);//微秒
        $token = md5($uid . $res . mt_rand(1, 9999));

        $data['user_id']    = $uid;
        $data['user_token'] = $token;
        $data['expires']    = time() + 86400 * 30;
        $where              = '(user_id,user_token,expires) values (:user_id,:user_token,:expires)';
        $arr = Sys::app()->db('dev')->setText("select * from bi_user_login_token where user_id=:user_id")->bindArray(['user_id'=>$uid])->queryRow();
        if($arr){
            $res = Sys::app()->db('dev')->setText("update bi_user_login_token set user_token = :user_token,expires = :expires where user_id=:user_id")->bindArray($data)->execute();
        }else{
            $where = '(user_id,user_token,expires) values (:user_id,:user_token,:expires)';
            $res = Sys::app()->db('dev')->setText("insert into bi_user_login_token".$where)->bindArray($data)->execute();
        }
        return $token;
    }

    public function think_ucenter_md5($str)
    {
        $key = '<rlvXL^B3YM~u2%|7]m9$IG_o)ADFNd:j*"J5zh&';
        return md5(sha1($str) . $key);
    }
}
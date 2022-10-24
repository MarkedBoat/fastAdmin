<?php

namespace modules\bee_invasion\v1\api\admin\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;

class ActionAdminLogin extends ActionBase
{


    public function run()
    {
        //测试
        $type          = urldecode($_REQUEST['type']);//1 用户名密码登录 3 验证码登录
        $data['is_ok'] = 1;
        $where         = 'is_ok = :is_ok';
        if ($type == 1)
        {
            $password = $this->inputDataBox->getStringNotNull('password');
            $password = $this->getPwd($password);
            if (!$password)
            {
                throw  new \Exception('解密失败', 400);
            }
//            $pImgCode = $this->inputDataBox->getStringNotNull('imgCode');
//            if($pImgCode != Sys::app()->params['checkCode']['smscode']){
//                $ss          = urldecode($_REQUEST['ss']);
//                $imgCode  = Sys::app()->redis('cache')->get($ss);
//                if ($imgCode != strtoupper($pImgCode))
//                {
//                    throw  new \Exception('图形验证码不正确', 400);
//                }
//            }

            $data[':username'] = $this->inputDataBox->getStringNotNull('userName');
            $where             .= " and (real_name=:username)";
        }
        else
        {
            //$redis = new Redis();
            $data['mobile'] = $this->inputDataBox->getStringNotNull('mobile');
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
        }
        $arr = Sys::app()->db('dev')->setText("select * from bi_bg_admin where " . $where)->bindArray($data)->queryRow();
        if (!$arr)
        {
            throw  new \Exception('登录失败，用户不存在或已被禁用！', 400);
        }
        else
        {
            //密码登录 判断密码
            if ($type == 1)
            {
                if ($this->think_ucenter_md5($password) != $arr['password'])
                {
                    $loginNum = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserloginCache']['key'] . $arr['id']);
                    if(!$loginNum){
                        $loginNum = 1;
                        Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg']['UserloginCache']['key'] . $arr['id'],$loginNum,60*5);
                    }else{
                        if ($loginNum >2)
                        {
                            throw  new \Exception('错误次数过多，请30分钟后尝试', 400);
                        }else{
                            $loginNum = Sys::app()->redis('cache')->incrBy(Sys::app()->params['cache_cfg']['UserloginCache']['key'] . $arr['id'],1);
                        }
                    }
                    throw  new \Exception('密码错误+'.$loginNum.',五次后将暂停登录！', 400);
                }
            }
            //校验后台权限
            $userNode = Sys::app()->db('dev')->setText("select * from bi_bg_rbac_user_role where user_id = :user_id")->bindArray(['user_id'=>$arr['id']])->queryRow();
            if(!$userNode){
                throw new \Exception('该账号没有权限');
            }
            //增加token
            $token = $this->getToken($arr['id']);
            $res   = ['code' => 'ok','status' => 200, 'msg' => '登录成功', 'user_id' => $arr['id'], 'userName' => $arr['real_name'] ?? '', 'token' => $token];
        }
        echo json_encode($res);exit;
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

    //生成token的操作
    //type 1 注册 2 登录
    public function getToken($uid)
    {
        $res   = microtime(true);//微秒
        $token = md5($uid . $res . mt_rand(1, 9999));

        $data['user_id']    = $uid;
        $data['user_token'] = $token;
        $data['expires']    = time() + 86400 * 1;
        $where              = '(user_id,user_token,expires) values (:user_id,:user_token,:expires)';
        $arr                = Sys::app()->db('dev')->setText("insert into bi_bg_admin_token" . $where . "ON DUPLICATE KEY UPDATE user_token = :user_token,expires = :expires ")->bindArray($data)->execute();
        return $token;
    }

    public function think_ucenter_md5($str)
    {
        $key = '<rlvXL^B3YM~u2%|7]m9$IG_o)ADFNd:j*"J5zh&';
        return md5(sha1($str) . $key);
    }

    public function checkPwd($password, $pwd)
    {
        $result['code'] = '0';
        if (strlen($pwd) < 8 || strlen($pwd) > 16)
        {
            $result['code'] = '1';
            $result['msg']  = '密码长度不符合';
            return $result;
        }
        //全数字情况
        if (preg_match("/^\d*$/", $pwd))
        {
            $result['code'] = '1';
            $result['msg']  = '密码必须包含字母';
            return $result;
        }
        //全英文情况
        if (preg_match("/^[a-z]*$/i", $pwd))
        {
            $result['code'] = '1';
            $result['msg']  = '密码必须包含数字';
            return $result;
        }
        //是否有特殊字符
        if (!preg_match("/^[a-z\d]*$/i", $pwd))
        {
            $result['code'] = '1';
            $result['msg']  = '密码只能包含数字和字母';
            return $result;
        }
        if ($password != $pwd)
        {
            $result['code'] = '1';
            $result['msg']  = '前后密码不一致，请重新检查';
            return $result;

        }
        return $result;
    }
}
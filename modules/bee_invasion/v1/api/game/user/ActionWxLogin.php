<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\user\ActionLogin;


class ActionWxLogin extends ActionBase
{


    public function run()
    {
        $data1['is_ok'] = 1;
        $where         = 'is_ok = :is_ok';
        //var_dump($_REQUEST);die;
        $password = $this->inputDataBox->getStringNotNull('password');
        $login = new ActionLogin();
        $password = $login->getPwd($password);
        if (!$password)
        {
            throw  new \Exception('解密失败', 400);
        }
        $data1[':nickname'] = $this->inputDataBox->getStringNotNull('nickname');
        $where             .= " and (nickname=:nickname or mobile = :nickname)";
        $arr = Sys::app()->db('dev')->setText("select * from bi_user where " . $where)->bindArray($data1)->queryRow();
        if (!$arr)
        {
            throw  new \Exception('登录失败，用户不存在或已被禁用！', 400);
        }
        if ($login->think_ucenter_md5($password) != $arr['password'])
        {
            throw  new \Exception('密码错误！', 400);
        }

        $code = rawurldecode(trim($_REQUEST['code']));
        $encryptedData = rawurldecode(trim($_REQUEST['encryptedData']));
        $iv = rawurldecode(trim($_REQUEST['iv']));
        $appid = Sys::app()->params['wxConfig']['appId'];//  wx3fc918e107035406
        $secret = Sys::app()->params['wxConfig']['appSercet'];
        $url= "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $uinfo = json_decode($this->curlPostContents($url),true);
        if($uinfo['openid']){
            $data['openid'] = $uinfo['openid'];
            //检查该微信号是否绑定其他账号
            $arr1 = Sys::app()->db('dev')->setText("select * from bi_user_weixin where openid = :openid")->bindArray($data)->queryRow();
            if($arr1){
                throw new \Exception('该微信号已绑定其他游戏账号',400);
            }

            $data['user_id'] = $arr['id'];
            $where = '(user_id,openid) values (:user_id,:openid)';
            $res = Sys::app()->db('dev')->setText("insert into bi_user_weixin" . $where)->bindArray($data)->execute();
            if($res){
                echo json_encode(['status'=>200,'code'=>'ok','msg'=>'微信绑定成功']);exit;
            }else{
                throw  new \Exception('微信绑定失败', 400);
            }
        }else{
            //登录错误
            throw  new \Exception('微信登录失败，请重新登录', 400);
        }
    }

    public function curlPostContents($url,$data=array(),$port=80)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if(strstr($url,'https'))
        {
            $port = 443;//https的端口号
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if($data)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_PORT,$port);
            curl_setopt($ch, CURLOPT_URL, $url);
            if($header){
                curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            }else{
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));

            }
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
        }
        $data = curl_exec($ch);
        if(curl_errno($ch)){
            $data = curl_error($ch);
            //$this->Tlog('url--'.$url.'请求失败：'.$data,'Weixin/check','data');
            $data = array();
        }
        curl_close($ch);
        //dump($data);
        return $data;
    }
}
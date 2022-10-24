<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\user\ActionLogin;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\user\User;


class ActionRegister extends ActionBase
{


    public function run()
    {
        $res = $this->userRegister();
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $res['data']['op_flag'] = $op_flag;
        }
        echo json_encode($res);exit;
    }

    //用户注册
    public function userRegister()
    {
        $result          = ['status' => '200', 'code' => 'ok'];
        $data[':mobile'] = $this->inputDataBox->getStringNotNull('mobile');
        //校验手机号是否已注册
        $res = Sys::app()->db('dev')->setText("select * from bi_user where mobile=:mobile")->bindArray($data)->queryRow();
        if ($res)
        {
            throw  new \Exception('该手机号已注册', 400);
        }
        if (isset($_REQUEST['cdkey']) && $_REQUEST['cdkey'])
        {
            $cdkey = $this->inputDataBox->getStringNotNull('cdkey');
//            $cdkey =  base_convert($cdkey,36,10);
            $cdkey = User::openCode2TrueId($cdkey);
            $res1 = Sys::app()->db('dev')->setText("select id from bi_user where id=:id")->bindArray(['id'=>$cdkey])->queryRow();
            if(!$res1){
                throw  new \Exception('邀请码不存在', 400);
            }
        }
        $userModel = new User();
        $userModel->mobile = $data[':mobile'];
        $userModel->nickname = substr_replace($data[':mobile'],'****',3,4);
        $login = new ActionLogin();
        //前后密码校验
        $password = $this->inputDataBox->tryGetString('password');
        if($password){

            //解密的是md5处理过得密码
            $password = $login->getPwd($password);
            //$where    = '(mobile,password) values (:mobile,:password)';
            //再次加密入库

            $data[':password'] = $login->think_ucenter_md5($password);
            $userModel->password = $data[':password'];
        }

        //短信验证码
        $smscode   = $this->inputDataBox->getStringNotNull('smscode');
        if($smscode != Sys::app()->params['checkCode']['smscode']){
            $rediscode = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data[':mobile']);
            if ($smscode != $rediscode)
            {
                throw  new \Exception('验证码不正确或失效，请重新获取', 400);
            }
            Sys::app()->redis('cache')->del(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $data[':mobile']);
        }

//        $area_code = $this->inputDataBox->tryGetString('area_code');
//        if($area_code)
//        {
//            $data1['area_code'] = $area_code;
//            //判断区域编码是否正确
//            $areaArr = Sys::app()->db('dev')->setText("select * from bi_area where area_code = :area_code")->bindArray($data1)->queryRow();
//            if (!$areaArr)
//            {
//                throw  new \Exception('区域编码不正确或失效，请重新获取', 400);
//            }
//        }


        //注册入库
        //$res = Sys::app()->db('dev')->setText("insert into bi_user" . $where)->bindArray($data)->execute();

        $userModel->insert(true);
        $userModel->generateOpenId();
        $uid = $userModel->id;
        if (!$uid)
        {
            throw  new \Exception('插入失败', 400);
        }
        //邀请码 8.29 改为邀请人
        if (isset($_REQUEST['cdkey']) && $_REQUEST['cdkey'])
        {
            $invite = new UserInviterDao();
            $invite->inviter_id = $res1['id'];
            $invite->be_invited_id = $uid;
            $invite->insert(true);
        }
        //增加地区
//        if($area_code){
//            $data1['user_id'] = $uid;
//            $arr                = Sys::app()->db('dev')->setText("insert into bi_user_profile (user_id,area_code) values (:user_id,:area_code) ")->bindArray($data1)->queryRow();
//        }
        //增加token
        $token             = $login->getToken($uid);
        $result['user_id'] = $uid;
        $result['token']   = $token;
        return $result;

    }

}
<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\sms\SignatureHelper;
use models\ext\tool\Code;
use models\common\db\Redis;


class ActionSms extends ActionBase
{


    public function run()
    {
        $type = urldecode($_REQUEST['type']);//1 发放验证码 2 验证码校验
        if ($type == 1)
        {
            $res = $this->sendSms();
        }
        else
        {
            $ss = $_REQUEST['ss'];
            $Code = new Code();
            $res = $Code->make($ss);
            $result = ['code'=>'ok','status'=>200,'data'=>$res];
//            $op_flag = $this->inputDataBox->tryGetString('op_flag');
//            if($op_flag){
//                $result['data']['op_flag'] = $op_flag;
//            }
            echo json_encode($result);exit;
            die;
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $res['data']['op_flag'] = $op_flag;
        }
        echo json_encode($res);exit;

    }

    /**
     * 发送短信
     */
    public function sendSms()
    {
        $result = ['code' => 0, 'msg' => '发送失败'];
        $mobile = urldecode($_REQUEST['mobile']);

        //限制条件一分钟一条 一天20条
        $arr = Sys::app()->redis('cache')->get(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $mobile);
//        if ($arr)
//        {
//            throw  new \Exception('两分钟内不能重复发送', 400);
//            //return ["status" => 0,'msg'=>'3分钟内不能重复发送'];
//        }
        $cacheName = Sys::app()->params['cache_cfg']['UserDayCodeSum']['key'] . date('Ymd') . $mobile;
        $dayCount = Sys::app()->redis('cache')->get($cacheName);
        if($dayCount){
            $ss = $_REQUEST['ss']??0;
            $pImgCode = $_REQUEST['imgCode']??0;
            if(!$ss || !$pImgCode){
                echo json_encode(['code'=>'noImgCode','msg'=>'缺少图形验证码','status'=>200]);exit;
                //throw  new \Exception('缺少图形验证码', 200);
            }
            if($pImgCode != Sys::app()->params['checkCode']['smscode']){
                $imgCode  = Sys::app()->redis('cache')->get($ss);
                if ($imgCode != strtoupper($pImgCode))
                {
                    echo json_encode(['code'=>'errorImgCode','msg'=>'图形验证码不正确或已过期','status'=>200]);exit;
                    //throw  new \Exception(, 200);
                }
            }
        }
        if ($dayCount > 30)
        {
            throw  new \Exception('今日发送信息数量已达上限', 400);
        }
        $dayCount || $dayCount = 0;
        $sms_code = $this->randCode(6, 1);
        $params   = array();

        $security = false;

        $accessKeyId     = "LTAI5tHcTYiQ66MXtUwFsgiF";
        $accessKeySecret = "RAkEXkwVObEFIBfJHi9EsbijcJ1MnE";

        $params["PhoneNumbers"] = $mobile;

        $params["SignName"] = "安徽爱之恋网络科技公司";

        $params["TemplateCode"] = "SMS_234490207";

        $params['TemplateParam'] = Array(
            "code" => $sms_code,
        );

        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"]))
        {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $helper = new SignatureHelper();

        $content = $helper->request($accessKeyId, $accessKeySecret, "dysmsapi.aliyuncs.com", array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action"   => "SendSms",
                "Version"  => "2017-05-25",
            )), $security);
        if ($content->Code == 'OK')
        {
            //保存验证码信息 存缓存测试 三分钟
            //$redis->set($mobile,$sms_code,180);
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg']['UserCheckCode']['key'] . $mobile, $sms_code, 120);
            //天限制
            Sys::app()->redis('cache')->set($cacheName, ++$dayCount, 60 * 60 * 24);
            //$redis->set('sms_count_' . date('Ymd') . $mobile, ++$dayCount, 60 * 60 * 24);
            $result = ['code' => 'ok','status'=>200, 'msg' => '发送成功'];
        }
        else
        {
            throw  new \Exception('发送失败'.$content->Message, 400);
            //$result = ['code' => 0, 'msg' => '发送失败', 'msgInfo' => $content->Message];
        }
        return $result;

    }

    public function encoding($str, $urlCode)
    {
        if (!empty($str))
        {
            $fileType = mb_detect_encoding($str, array('UTF-8', 'GBK', 'LATIN1', 'BIG5'));
        }
        return mb_convert_encoding($str, $urlCode, $fileType);
    }

    /**
     * get请求
     * @param $url
     * @param $params
     * @return false|string
     */
    function send_get($url, $params)
    {
        $getdata = http_build_query($params);
        return file_get_contents($url . '?' . $getdata);
    }


    /**
     * post请求
     * @param $url
     * @param $params
     * @return bool|string
     */
    function send_post_curl($url, $params)
    {

        $postdata = http_build_query($params);
        $length   = strlen($postdata);
        $cl       = curl_init($url);          //①：初始化
        curl_setopt($cl, CURLOPT_POST, true); //②：设置属性
        curl_setopt($cl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($cl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-length: " . $length));
        curl_setopt($cl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($cl); //③：执行并获取结果
        curl_close($cl);           //④：释放句柄
        return $content;
    }

    /**
     * +----------------------------------------------------------
     * 生成随机字符串
     * +----------------------------------------------------------
     * @param int $length 要生成的随机字符串长度
     * @param int $type 随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符；5，数字+大写字母；
     * +----------------------------------------------------------
     * @return string
     * +----------------------------------------------------------
     */
    function randCode($length = 5, $type = 0)
    {
        $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0)
        {
            array_pop($arr);
            $string = implode("", $arr);
        }
        elseif ($type == "-1")
        {
            $string = implode("", $arr);
        }
        elseif ($type == "5")
        {
            $array  = array($arr[1], $arr[3]);
            $string = implode("", $array);
        }
        else
        {
            $string = $arr[$type];
        }
        //sprintf('%x',crc32(microtime()))
        $count = strlen($string) - 1;
        $code  = '';
        for ($i = 0; $i < $length; $i++)
        {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }
}
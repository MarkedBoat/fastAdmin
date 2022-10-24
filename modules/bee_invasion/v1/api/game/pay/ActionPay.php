<?php

namespace modules\bee_invasion\v1\api\game\pay;

use models\common\ActionBase;
use models\common\sys\Sys;

//提现申请打钱 暂时不用
class ActionPay extends ActionBase
{
    public function run()
    {
        $openid = 'ofaV35ccPaTLJ35g3h8W3GewZg20';
        $url    = Sys::app()->params['wxConfig']['transUrl'];
        //$out_trade_no = time() . env('wx.appid') . $openid . rand(10, 99);
        $data = [
            'mch_appid'        => Sys::app()->params['wxConfig']['appId'],//商户账号appid
            'mchid'            => Sys::app()->params['wxConfig']['mchId'],//商户号
            'nonce_str'        => $this->createNoncestr(),//随机字符串
            'partner_trade_no' => date('YmdHis') . rand(1000, 9999),//商户订单号
            'openid'           => $openid,//用户openid
            'check_name'       => 'NO_CHECK',//校验用户姓名选项,
            're_user_name'     => 'zsq',//收款用户姓名
            'amount'           => '30',//金额
            'desc'             => '蜜蜂入侵提现',//企业付款描述信息
            'spbill_create_ip' => $this->getIP(),//Ip地址

        ];
        //生成签名算法
        $data['sign'] = $this->createSign($data);
        $xml          = $this->arraytoxml($data);
        $res          = $this->curl_post_ssl($url, $xml);
        $return       = $this->xmltoarray($res);


        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        $res         = $responseObj->return_code;
        if($responseObj->return_code == 'SUCCESS'){
            if(!$responseObj->return_msg){
                return $res;
            }else{
                throw  new \Exception($responseObj->return_msg, 400);
            }
        }else{
            throw  new \Exception('微信接口调用失败', 400);
        }
        //echo $res= $responseObj->return_code; //SUCCESS 如果返回来SUCCESS,则发生成功，处理自己的逻辑

    }

    function getIP()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }

    /**
     * 生成校验Sign
     *
     * @param array $param 待校验数据
     *
     * @return string 签名
     * @throws \Exception
     */
    private function createSign($param): string
    {
        $param = array_filter($param);
        ksort($param);
        $string = "";
        foreach ($param as $k => $v)
        {

            if ($k != "sign" && $v != "" && !is_array($v))
            {
                $string .= $k . "=" . $v . "&";
            }
        }
        $string = $string . "key=" . Sys::app()->params['wxConfig']['paySecret'];
        $string = md5($string);
        return strtoupper($string);
    }

    /**
     * [xmltoarray xml格式转换为数组]
     * @param [type] $xml [xml]
     * @return mixed [type]  [xml 转化为array]
     */
    function xmltoarray($xml)
    {
        //禁止引用外部xml实体
        //libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val       = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    /**
     * [arraytoxml 将数组转换成xml格式（简单方法）:]
     * @param [type] $data [数组]
     * @return string [type]  [array 转 xml]
     */
    function arraytoxml($data)
    {
        $xml = "<xml>";
        foreach ($data as $key => $val)
        {
            if (is_numeric($val))
            {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
            else
            {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * [createNoncestr 生成随机字符串]
     * @param integer $length [长度]
     * @return string [type]   [字母大小写加数字]
     */
    function createNoncestr($length = 32)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";

        for ($i = 0; $i < $length; $i++)
        {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param $url
     * @param $xmldata
     * @param int $second
     * @param array $aHeader
     * @return bool|string [type]   [description]
     */
    function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array())
    {
        $isdir = $_SERVER['DOCUMENT_ROOT'] . "/static/file/cert/";//证书位置;绝对路径

        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);                      //设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                     //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);                             //抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                 // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);                 //
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');                    //证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');                     //CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');  //证书位置
        // curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        // curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);             //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error";
            curl_close($ch);
            return false;
        }
    }


}
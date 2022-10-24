<?php

namespace modules\bee_invasion\v1\api\admin\pay;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\cache\ApiCache;


class ActionDoPay extends AdminBaseAction
{
    public function run()
    {
        $result = ['status' => 400, 'code' => 'false', 'msg' => '失败'];
        $id     = $this->inputDataBox->getStringNotNull('id');

        $data['id']     = $id;
        $res            = Sys::app()->db('dev')->setText("select * from bi_user_apply_pay where id = :id and status = 1 and is_ok = 1")->bindArray($data)->queryRow();
        $data['status'] = $this->inputDataBox->getStringNotNull('status');
        if (!$res)
        {
            throw  new \Exception('申请记录不存在', 400);
        }
        Sys::app()->db('dev')->beginTransaction();
        if ($data['status'] == 3)
        {//审核不通过 须填写申请原因
            $data['reason'] = $this->inputDataBox->getStringNotNull('reason');
            $res            = Sys::app()->db('dev')->setText("update bi_user_apply_pay set status = :status,reason = :reason where id = :id")->bindArray($data)->execute();
        }
        else
        {
            //审核通过
            //判断剩余元宝数量
            $gold = Sys::app()->db('dev')->setText("select id,item_amount from bi_user_currency where user_id=:user_id and item_code = 'gold_ingot'")->bindArray(['user_id' => $res['user_id']])->queryRow();
            if (!$gold || $gold['item_amount'] - $res['gold_ingot'] < 0)
            {
                throw new \Exception('剩余元宝数量不足，请重新检查', 400);
            }
            //扣除相应元宝
            $awardData['user_id'] = $res['user_id'];
            $awardData['item_amount'] = '-'.$res['gold_ingot'];
            $awardData['item_code']   = 'gold_ingot';
            $currency = Sys::app()->db('dev')->setText("update bi_user_currency set item_amount = item_amount-:num where id = :id")->bindArray(['num'=>$res['gold_ingot'],'id'=>$gold['id']])->execute();
            $awardData['curr_amount'] = $gold['item_amount'];
            $awardData['expect_amount'] = $gold['item_amount'] - $res['gold_ingot'];
            $awardData['src_id'] = time();
            $awardData['src_op_type'] = 2;
            $awardData['src'] = 'order_apply';
            $hiswhere = '(user_id,item_code,item_amount,curr_amount,expect_amount,src_id,src,src_op_type) values (:user_id,:item_code,:item_amount,:curr_amount,:item_amount+:curr_amount,:src_id,:src,:src_op_type)';
            //添加操作记录
            $currhis = Sys::app()->db('dev')->setText("insert into bi_user_currency_his".$hiswhere)->bindArray($awardData)->execute();

            //修改提现状态
            $changeRes                = Sys::app()->db('dev')->setText("update bi_user_apply_pay set status = :status where id = :id")->bindArray($data)->execute();
            if(!$changeRes || !$currency || !$currhis){
                Sys::app()->db('dev')->rollBack();
                throw new \Exception('提现状态修改失败', 400);
            }
            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $this->user->id], time());
            if ($res['type'] == 1)
            {//微信
                // 打钱
                $responseObj = $this->doPay($res['receive_money'], $res['openid']);
                $res         = $responseObj->return_code;
                if ($responseObj->return_code == 'SUCCESS')
                {
                    if ($responseObj->return_msg)
                    {
                        Sys::app()->db('dev')->rollBack();
                        throw  new \Exception($responseObj->return_msg, 400);
                    }
                }
                else
                {
                    Sys::app()->db('dev')->rollBack();
                    throw  new \Exception('微信接口调用失败', 400);
                }

            }
            elseif($res['type'] == 2)
            {
                $aliData                  = json_decode($res['description'], true);
                $aliData['order_id']      = $res['order_id'];
                $aliData['openid']        = $res['openid'];
                $aliData['receive_money'] = $res['receive_money'];
                $res                      = $this->aliDoPay($aliData);
                if ($res['data']['msg'] != 'Success')
                {
                    Sys::app()->db('dev')->rollBack();
                    throw new \Exception($res['data']['subMsg'], 400);
                }
            }
        }

        $result = ['status' => 200, 'code' => 'ok', 'msg' => '成功'];

        Sys::app()->db('dev')->commit();
        echo json_encode($result);
        exit;
    }

    public function aliDoPay($data)
    {
        $url = 'taojin.aiqingyinghang.com/prod-api/third/alipay/alipayFundTransUniTransfer';
        //调用支付宝接口
        $alidata = [
            'outBizNo'    => $data['order_id'],
            'transAmount' => $data['receive_money'],
            'orderTitle'  => '蜜蜂入侵',
            'remark'      => '蜜蜂入侵收益',
            'payeeInfo'   => [
                'identityType' => 'ALIPAY_LOGON_ID',
                'identity'     => $data['openid'],
                'name'         => $data['name'],
            ],
        ];
        $alidata = json_encode($alidata, JSON_UNESCAPED_UNICODE);
        return json_decode($this->json_post($url, $alidata), true);
    }

    public function doPay($money, $openid)
    {
        //后台提现审核
        //$opneid = $this->inputDataBox->getStringNotNull('opneid');
        //$money = $this->inputDataBox->getStringNotNull('money'); //单位改为元
        //$money *= 100;//元转分
        $money  = 30;//单位为分 最低30
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
            'amount'           => $money,//金额
            'desc'             => '蜜蜂入侵提现',//企业付款描述信息
            'spbill_create_ip' => $this->getIP(),//Ip地址

        ];
        //生成签名算法
        $data['sign'] = $this->createSign($data);
        $xml          = $this->arraytoxml($data);
        $res          = $this->curl_post_ssl($url, $xml);
        $return       = $this->xmltoarray($res);
        //var_dump($return);die;
        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $responseObj;
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

    public function json_post($url, $data = NULL)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!$data)
        {
            throw  new \Exception('提现数据为空', 400);
        }
        if (is_array($data))
        {
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res     = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno)
        {
            throw  new \Exception($errorno, 400);
        }
        curl_close($curl);
        return $res;

    }


}
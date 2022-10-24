<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\api\open\OpenBaseAction;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;


class ActionDebugOrderInfo extends ActionBase
{
    public function run()
    {
        Sys::app()->setDebug(true);
        $true_order_id = $this->inputDataBox->getStringNotNull('true_order_id');

        try
        {
            $order = PlatOrderDao::model()->findByPk($true_order_id);
        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('order_not_found', '查找不到订单', false);
        }


        try
        {
            $res = $order->syncRemoteOrderInfo();
            if ($res === false)
            {
                return $this->dispatcher->createInterruption('get_order_info_fail', '查找不到订单', false);
            }
        } catch (\Exception $e)
        {
            return $this->dispatcher->createInterruption('get_order_info_fail', '查找不到订单', false);
        }


        $partner        = PlatSrcDao::model()->findOneByWhere(['src_code' => $order->order_src]);
        $pri_key        = $partner->pri_key;
        $info           = $order->getOpenInfo();
        $json           = json_encode($info);
        $now_ts         = time();
        $partner_code   = 'azl';
        $str            = "{$json}{$partner_code}{$now_ts}";
        $sign           = RSA::sign($str, $pri_key);
        $sign_urlencode = urlencode($sign);
        $uri            = "partner_code={$partner_code}&timestamp={$now_ts}&sign={$sign_urlencode}";
        $url            = $order->notify_url . (strstr($order->notify_url, '?') ? "&{$uri}" : "?{$uri}");
        //Curl::curlRequest($url);
        //return $order->getOpenInfo();
        echo "\n\nstr:\n{$str}\n";
        var_dump($str);
        echo "\n\nurl:\n{$url}\n";
        echo "\n\nbody:\n{$json}\n";
        var_dump($json);
        echo "\n\n";

        list($http_code, $respone_body) = (new Curl())->post2($url, $json, false, 5000, 5000, Curl::application_json);
        echo "\nhttp code:\n{$http_code}\n\nrespone body:\n{$respone_body}\n";
        $pub_key = $partner->pub_key;
        var_dump(RSA::verify($str, $sign, $pub_key));
        \models\Api::$hasOutput = true;
        die;
    }
}
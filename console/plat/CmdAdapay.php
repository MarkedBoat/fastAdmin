<?php

/**
 * Created by PhpStorm.
 * User: markedboat
 * Date: 2018/7/20
 * Time: 11:01
 */

namespace console\plat;

use models\common\CmdBase;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\model\economy\plat\Partner;

class CmdAdapay extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function handlePayedNotify()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} handlePayedNotify  start\n";

        $partner_queue_key_prefix = Sys::app()->params['pay']['partner_order_notify_queue_prefix'];
        $plat_queue_key           = Sys::app()->params['pay']['plat_order_notify_queue'];
        $redis                    = Sys::app()->redis('pay');
        $res                      = Sys::app()->redis('pay')->lRange($plat_queue_key, 0, 200);
        while (true)
        {
            if ($this->getCountdownSeconds() < 100)
            {
                if ((time() % 60) > 45)
                {
                    $deadLine = date('Y-m-d H:i:s', $this->deadLineTs);
                    $now_date = date('Y-m-d H:i:s', time());
                    echo "\nnow:{$now_date}  deadline:{$deadLine}  :自动退出\n";
                    break;
                }
            };
            $json = $redis->lPop($plat_queue_key);
            if (empty($json))
            {
                usleep(200);
                continue;
            }

            $now_ts   = time();
            $now_date = date('Y-m-d H:i:s', $now_ts);
            echo "\ndate:{$now_date}  handle  queue_key:{$plat_queue_key} json:{$json}\n";

            $redis->rPush("{$partner_queue_key_prefix}_review", json_encode([$now_ts + 600, $json]));


            $redis->rPush("{$partner_queue_key_prefix}_0s", json_encode([$now_ts, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_0s", json_encode([$now_ts, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_0s", json_encode([$now_ts, $json]));

            $redis->rPush("{$partner_queue_key_prefix}_2s", json_encode([$now_ts + 2, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_2s", json_encode([$now_ts + 2, $json]));

            $redis->rPush("{$partner_queue_key_prefix}_3s", json_encode([$now_ts + 3, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_3s", json_encode([$now_ts + 3, $json]));

            $redis->rPush("{$partner_queue_key_prefix}_5s", json_encode([$now_ts + 5, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_10s", json_encode([$now_ts + 10, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_30s", json_encode([$now_ts + 30, $json]));

            $redis->rPush("{$partner_queue_key_prefix}_1min", json_encode([$now_ts + 60, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_5min", json_encode([$now_ts + 300, $json]));

            $redis->rPush("{$partner_queue_key_prefix}_1hour", json_encode([$now_ts + 3600, $json]));
            $redis->rPush("{$partner_queue_key_prefix}_24hour", json_encode([$now_ts + 86440, $json]));

        }

    }

    public function notifyPartner()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} notifyPartner  start\n";
        $time_flag                          = $this->inputDataBox->getStringNotNull('time_flag');
        $partner_queue_key                  = Sys::app()->params['pay']['partner_order_notify_queue_prefix'] . '_' . $time_flag;
        $partner_order_notify_status_prifix = Sys::app()->params['pay']['partner_order_notify_status_prefix'];
        $redis                              = Sys::app()->redis('pay');
        while (true)
        {
            if ($this->getCountdownSeconds() < 100)
            {
                if ((time() % 60) > 45)
                {
                    $deadLine = date('Y-m-d H:i:s', $this->deadLineTs);
                    $now_date = date('Y-m-d H:i:s', time());
                    echo "\nnow:{$now_date}  deadline:{$deadLine}  :自动退出\n";
                    break;
                }
            };
            $json = $redis->lPop($partner_queue_key);
            if (empty($json))
            {
                usleep(200);
                continue;
            }
            $data_info = json_decode($json, true);
            if (!(is_array($data_info) && count($data_info) === 2))
            {
                echo " error_queue_element  DATA_INFO_ERROR queue_key:{$partner_queue_key} json:{$json}\n";
                var_dump($data_info);
                echo "\n";
                continue;
            }

            list($time, $order_json) = $data_info;
            $now_ts = time();
            $date   = date('Y-m-d H:i:s', $now_ts);
            if ($time > $now_ts)
            {
                echo " not_time_yet date:{$date} now:{$now_ts} > want:{$time} queue_key:{$partner_queue_key} json:{$json}\n";
                continue;
            }
            $order_info = json_decode($order_json, true);
            if (!(is_array($order_info) && count($order_info) > 10))
            {
                echo " error_queue_element ORDER_INFO_ERROR  queue_key:{$partner_queue_key} json:{$order_info}\n";
                var_dump($order_info);
                continue;
            }
            echo " date:{$date} time_flag:{$time_flag}  queue_key:{$partner_queue_key} time:{$time} order_inner_info_json:{$order_json}\n";
            $plat_order                      = PlatOrderDao::model()->loadData($order_info);
            $partner_order_notify_status_key = "{$partner_order_notify_status_prifix}_{$plat_order->id}";
            if ($redis->get($partner_order_notify_status_key) === 'ok')
            {
                echo "skip notify:  queue_key:{$partner_queue_key} order_id:{$plat_order->id}  open_order_id:{$plat_order->open_order_id}  partner_order_id:{$plat_order->src_order_id}  url:{$plat_order->notify_url}  ";
                $plat_order->recordNotify("{$time_flag}_{$date}_", false);
            }
            else
            {
                $partner         = Partner::model()->getModelByMainIndex($plat_order->order_src);
                $open_info       = $plat_order->getOpenInfo(false);
                $order_info_json = json_encode($open_info);
                $now_ts          = time();
                $sign            = urlencode($partner->getStringSign("{$order_info_json}{$partner->src_code}{$now_ts}"));
                $uri             = "partner_code={$partner->src_code}&timestamp={$now_ts}&sign={$sign}";
                $url             = $plat_order->notify_url . (strstr($plat_order->notify_url, '?') ? "&{$uri}" : "?{$uri}");;
                list($http_code, $response_text) = (new Curl())->setResponseMaxSize(10240)->post2($url, $order_info_json, false, 1000, 1000, Curl::application_json);
                echo "notify:  queue_key:{$partner_queue_key} order_id:{$plat_order->id}  open_order_id:{$plat_order->open_order_id}  partner_order_id:{$plat_order->src_order_id}  url:{$url}  data:{$order_info_json}   res->   http_code:{$http_code}  response_text:{$response_text}\n";
                $is_response = false;
                if (!empty($response_text) && strstr($response_text, 'SUCCESS'))
                {
                    $is_response = true;
                    $redis->set($partner_order_notify_status_key, 'ok', 86400);
                }
                Sys::app()->clearLogs();
                $record_res = $plat_order->recordNotify("{$time_flag}_{$date}", $is_response);
                // var_dump(Sys::app()->getLogs());

                var_dump($record_res);
                echo "\nRECORD\n";
            }
        }
    }


}
<?php

namespace modules\bee_invasion\v1\dao\game\economy;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\cache\ApiCache;

/**
 * @property int id
 * @property string order_src 来源，fc:阜藏商城
 * @property string open_order_id 对外展示的订单id
 * @property string src_order_id 来源订单id
 * @property string pay_channel 支付渠道 alipay:支付宝 app
 * @property string plat_order_id 支付平台的id
 * @property string plat_pay_url 平台支付地址
 * @property string plat_query_url 平台查询支付结果url
 * @property string payment_platform 支付平台, adapay/wechat/alipay
 * @property string title 订单名称
 * @property string detail 订单描述
 * @property int order_sum 订单金额，采用最小单位，现金为分
 * @property string notify_url 异步通知地址
 * @property string return_url 同步通知地址
 * @property string device_ip 支付设备ip
 * @property int is_payed 是否支付  1:yes  2:no
 * @property int is_complete 是否交付完成了
 * @property int is_refund 是否退库  1:yes  2:no
 * @property int is_notifyed 是否通知了 1:yes  2:no
 * @property int is_response 是否回应
 * @property string notify_record 通知次数
 * @property int is_cls 是否关闭 1:yes  2:no
 * @property string payed_time 支付时间
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class PlatOrderDao extends ORM
{
    public $id               = null;
    public $order_src        = 0;
    public $open_order_id    = 0;
    public $src_order_id     = 0;
    public $pay_channel      = 0;
    public $plat_order_id    = 0;
    public $plat_pay_url     = 0;
    public $plat_query_url   = 0;
    public $payment_platform = 0;
    public $title            = 0;
    public $detail           = 0;
    public $order_sum        = 0;
    public $notify_url       = 0;
    public $return_url       = 0;
    public $device_ip        = 0;
    public $is_payed         = 2;
    public $is_complete      = 2;
    public $is_refund        = 2;
    public $is_notifyed      = 2;
    public $is_response      = 2;
    public $notify_record    = null;
    public $is_cls           = 2;
    public $payed_time       = null;
    public $is_ok            = 1;
    public $create_time      = null;
    public $update_time      = null;


    public static $_fields_str;
    public static $tableName    = 'bi_plat_order';
    public static $pk           = 'id';
    public static $field_config = [
        'id'               => ['db_type' => 'int', 'length' => 0, 'def' => null, 'pro_def' => null],
        'order_src'        => ['db_type' => 'varchar', 'length' => 32, 'def' => 'fc', 'pro_def' => 0],
        'open_order_id'    => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'src_order_id'     => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'pay_channel'      => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'plat_order_id'    => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'plat_pay_url'     => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'plat_query_url'   => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'payment_platform' => ['db_type' => 'varchar', 'length' => 16, 'def' => '', 'pro_def' => 0],
        'title'            => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'detail'           => ['db_type' => 'varchar', 'length' => 64, 'def' => '', 'pro_def' => 0],
        'order_sum'        => ['db_type' => 'int', 'length' => 0, 'def' => 0, 'pro_def' => 0],
        'notify_url'       => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'return_url'       => ['db_type' => 'varchar', 'length' => 255, 'def' => '', 'pro_def' => 0],
        'device_ip'        => ['db_type' => 'varchar', 'length' => 32, 'def' => '', 'pro_def' => 0],
        'is_payed'         => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_complete'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_refund'        => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_notifyed'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'is_response'      => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'notify_record'    => ['db_type' => 'json', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_cls'           => ['db_type' => 'tinyint', 'length' => 0, 'def' => 2, 'pro_def' => 2],
        'payed_time'       => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
        'is_ok'            => ['db_type' => 'tinyint', 'length' => 0, 'def' => 1, 'pro_def' => 1],
        'create_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => 'CURRENT_TIMESTAMP', 'pro_def' => null],
        'update_time'      => ['db_type' => 'timestamp', 'length' => 0, 'def' => null, 'pro_def' => null],
    ];


    private $lastLastError = ['code' => '', 'msg' => '', 'debuginfo' => false];

    public function getDbConfName()
    {
        return 'bee_invade';
    }

    /**
     * @return static
     * @throws \models\common\error\AdvError
     */
    public function saveAndGenerateOrderOpenId()
    {
        $this->notify_record = [];
        $this->insert();
        $this->open_order_id = date(Sys::app()->params['pay']['plat_order_prefix'], time()) . (100000000 + $this->id);
        $this->update();
        return $this;
    }

    public static function openOrderId2TrueOrderId($fake_order_id)
    {
        return intval(substr($fake_order_id, 13)) - 100000000;
    }

    public function logErrorAndReturnFalse($code, $msg, $debug_info)
    {
        $this->lastLastError = ['code' => $code, 'msg' => $msg, 'debuginfo' => $debug_info];
        return false;
    }

    /**
     * 生成订单
     * @return $this|bool
     * @throws \models\common\error\AdvError
     */
    public function generateOrder()
    {
        try
        {
            $this->saveAndGenerateOrderOpenId();
        } catch (\Exception $e)
        {
            if (PlatOrderDao::model()->findOneByWhere(['order_src' => $this->order_src, 'src_order_id' => $this->src_order_id], false))
            {
                return $this->logErrorAndReturnFalse('partner_order_has_exist', '订单已经存在', false);
            }
            else
            {
                return $this->logErrorAndReturnFalse('create_order_fail', '创建订单失败，请稍后重试', false);
            }
        }


        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapaySdk/init.php';
        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapayDemo/config.php';

        $payment = new \AdaPaySdk\Payment();

        $payment_params = array(
            'app_id'      => Sys::app()->params['pay']['adapay_app_id'],
            'order_no'    => $this->open_order_id,
            'pay_channel' => $this->pay_channel,
            //'time_expire'=> date("YmdHis", time()+86400),
            'pay_amt'     => number_format(floatval($this->order_sum) / 100.00, 2),
            'goods_title' => $this->title,
            'goods_desc'  => $this->detail,
            'description' => $this->detail,
            'device_info' => ['device_p' => "111.121.9.10"],
            'notify_url'  => Sys::app()->params['pay']['pay_page_domain'] . '/bee_invasion/v1/game/economy/plat/adapayNotify',
        );

        Sys::app()->setForceLog()->addLog(['request' => $payment_params, 'open_info' => $this->getOuterDataArray()], 'open_order');
        try
        {
            # 发起支付
            $payment->create($payment_params);

            # 对支付结果进行处理
            if ($payment->isError())
            {
                //失败处理
                // var_dump($payment->result);
            }
            else
            {
                //成功处理
                //var_dump($payment->result);
            }
        } catch (\Exception $e)
        {
            return $this->logErrorAndReturnFalse('create_order_fail', '创建订单失败，请稍后重试1', false);
        }

        if (!(isset($payment->result['expend']['pay_info']) && isset($payment->result['id']) && isset($payment->result['query_url'])))
        {
            return $this->logErrorAndReturnFalse('create_order_fail', '创建订单失败，请稍后重试 2', false);
        }

        try
        {
            $this->plat_order_id  = $payment->result['id'];
            $this->plat_query_url = $payment->result['query_url'];
            $this->plat_pay_url   = $payment->result['expend']['pay_info'];
            $this->update();
        } catch (\Exception $e)
        {
            return $this->logErrorAndReturnFalse('create_order_fail', '创建订单失败，请稍后重试3', false);
        }
        return $this;
    }

    /**
     *
     * 查询 支付平台的信息
     * @return bool  true:查询成功  false:查询失败
     * @throws \models\common\error\AdvError
     */
    public function syncRemoteOrderInfo()
    {
        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapaySdk/init.php';
        include_once __ROOT_DIR__ . '/models/ext/pay/adapay_sdk/AdapayDemo/config.php';

        $payment = new \AdaPaySdk\Payment();

        $payment_params = array(
            "payment_id" => $this->plat_order_id,
        );

        # 发起支付
        $payment->query($payment_params);

        # 对支付结果进行处理
        if ($payment->isError())
        {
            //失败处理
            // var_dump($payment->result);
        }
        else
        {
            //成功处理
            // var_dump($payment->result);
        }
        if (!isset($payment->result['status']))
        {
            return false;
        }

        if ($payment->result['status'] === "succeeded")
        {
            if ($this->is_payed !== Opt::isOk)
            {
                $this->is_payed   = Opt::isOk;
                $this->payed_time = date('Y-m-d H:i:s', time());
                $this->update();
            }
        }
        return true;

    }

    public function getOpenInfo($get_pay_url = true)
    {
        return [
            'id'               => $this->id,
            'open_order_id'    => $this->open_order_id,
            'partner_code'     => $this->order_src,
            'partner_order_id' => $this->src_order_id,
            'pay_channel'      => $this->pay_channel,
            'pay_url'          => $get_pay_url ? $this->getPayUrl() : '',
            //'plat_order_id' => $this->plat_order_id,
            // 'payment_platform' => $this->payment_platform,
            'title'            => $this->title,
            'detail'           => $this->detail,
            'order_sum'        => $this->order_sum,
            'notify_url'       => $this->notify_url,
            'device_ip'        => $this->device_ip,
            'is_payed'         => $this->is_payed === Opt::isOk ? 'yes' : 'no',
            //'is_complete'   => $this->is_complete === Opt::isOk ? 'yes' : 'no',
            'is_refund'        => $this->is_refund === Opt::isOk ? 'yes' : 'no',
            'is_notifyed'      => $this->is_notifyed === Opt::isOk ? 'yes' : 'no',
            'is_closed'        => $this->is_cls === Opt::isOk ? 'yes' : 'no',
            'payed_time'       => $this->payed_time,
            'is_ok'            => $this->is_ok === Opt::isOk ? 'yes' : 'no',
            'create_time'      => $this->create_time,
            //'update_time'   => $this->update_time,
        ];
    }

    public function cacheOpenInfo()
    {
        ApiCache::model()->setCache('AdapayOrderOpenInfo', ['open_order_id' => $this->open_order_id], $this->getOpenInfo(false));
        ApiCache::model()->getCache('AdapayOrderOpenInfo', ['open_order_id' => $this->open_order_id]);
    }


    public function getQuickQueryUrl()
    {
        return Sys::app()->params['pay']['pay_page_domain'] . '/bee_invasion/v1/game/economy/plat/qQuery?order_id=' . $this->open_order_id . '&sign=' . $this->getQuickQuerySign($this->open_order_id);
    }

    public function getQuickQuerySign($open_order_id)
    {
        return substr(md5(Sys::app()->params['pay']['secret_md5'] . $open_order_id), 8, 10);
    }

    public function getPayUrl()
    {
        $data = [
            //'id'          => $this->id,
            //'pay_url'     => 'https://markedboat.com/imark/pay?url=' . urlencode($this->plat_pay_url),
            'plat_order_id' => $this->plat_order_id,
            // 'payment_platform' => $this->payment_platform,
            'title'         => $this->title,
            'detail'        => $this->detail,
            'order_sum'     => $this->order_sum,
            //'notify_url'  => $this->notify_url,
            'return_url'    => $this->return_url,
            'create_time'   => $this->create_time,
            //'update_time'   => $this->update_time,
            'query_url'     => $this->getQuickQueryUrl(),
        ];
        return Sys::app()->params['pay']['pay_page_domain'] . '/bee_invasion/v1/game/economy/plat/pay?alipay_app=' . urlencode($this->plat_pay_url) . '&order_info=' . urlencode(json_encode($data));
    }

    /**
     * @param $record_flag
     * @param $is_response
     * @return int
     * @throws \Exception
     */
    public function recordNotify($record_flag, $is_response = false)
    {
        $tn = $this->getTableName();
        if ($is_response)
        {
            $sql = "update {$tn} set is_notifyed=:is_notifyed,is_response=:is_response,notify_record=json_array_append(notify_record, '$', :val) where id=:id;";
            return $this->getDbConnect()->setText($sql)->bindArray([':is_notifyed' => Opt::isOk, ':is_response' => Opt::isOk, ':val' => $record_flag, ':id' => $this->id])->execute();
        }
        else
        {
            $sql = "update {$tn} set is_notifyed=:is_notifyed,notify_record=json_array_append(notify_record, '$', :val) where id=:id;";
            return $this->getDbConnect()->setText($sql)->bindArray([':is_notifyed' => Opt::isOk, ':val' => $record_flag, ':id' => $this->id])->execute();
        }


    }


}
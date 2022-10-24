<?php
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: leilei.yang
 * Date: 2021/4/20
 * Time: 16:49
 */
class AdaPayCommonTest extends TestCase
{
    public function testQueryPayment()
    {
        AdaPay\AdaPay::$gateWayType = 'api';
        // 查询账户余额
        $obj = new \AdaPaySdk\AdaPayCommon();
        $params = array(
            "adapay_func_code" => "payments.002112021042017103310230508701258551296",
            "adapay_api_version" => "v1",
            'payment_id'=> '002112021042017103310230508701258551296'
        );
        $obj->queryAdapay($params);

        print("查询账户余额".$obj->isError().'=>'.json_encode($obj->result)."\n");
        $this->assertEquals('pending', $obj->result['status']);
    }

    public function testCreatePayments() {
        AdaPay\AdaPay::$gateWayType = 'api';
        # 初始化对象
        $commonObj = new \AdaPaySdk\AdaPayCommon();

        $params = array(
            "adapay_func_code" => "payments",
            "adapay_api_version" => "v1",

            'app_id'=> 'app_7d87c043-aae3-4357-9b2c-269349a980d6',
//    'app_id'=> 'app_f7841d17-8d4e-469f-82da-1c3f43c3e470',
            'order_no'=> "PY_". date("YmdHis").rand(100000, 999999),
            'pay_channel'=> 'alipay',
            'time_expire'=> date("YmdHis", time()+86400),
            'pay_amt'=> '0.01',
            'goods_title'=> 'subject',
            'goods_desc'=> 'body',
            'description'=> 'description',
            'device_id'=> ['device_id'=>"1111"],
            'expend'=> [
                'buyer_id'=> '1111111',              // 支付宝卖家账号ID
                'buyer_logon_id'=> '22222222222',   // 支付宝卖家账号
                'promotion_detail'=>[              // 优惠信息
                    'cont_price'=> '100.00',      // 订单原价格
                    'receipt_id'=> '123',        // 商家小票ID
                    'goodsDetail'=> [           // 商品信息集合
                        ['goods_id'=> "111", "goods_name"=>"商品1", "quantity"=> 1, "price"=> "1.00"],
                        ['goods_id'=> "112", "goods_name"=>"商品2", "quantity"=> 1, "price"=> "1.01"]
                    ]
                ]
            ]
        );

        $commonObj->requestAdapay($params);
        print($commonObj->isError().'=>'.json_encode($commonObj->result));
        $this->assertEquals('succeeded', $commonObj->result['status']);
    }

    public function testFastCardApply() {
        AdaPay\AdaPay::$gateWayType = 'page';
        # 初始化对象
        $commonObj = new \AdaPaySdk\AdaPayCommon();

        $params = array(
            "adapay_func_code" => "fast_card.apply",
            "adapay_api_version" => "v1",
            'app_id'=> 'app_7d87c043-aae3-4357-9b2c-269349a980d6',
            'card_id'=> '666666666666666666666666',
            'tel_no'=> '13888888888',
            'member_id'=> 'member_id_test',
            'vip_code'=> '321',
            'expiration'=> '0225'
        );
        $commonObj->requestAdapayUits($params);
        print("创建快捷支付绑卡对象".$commonObj->isError().'=>'.json_encode($commonObj->result)."\n");
        $this->assertEquals('failed', $commonObj->result['status']);
    }

    public function testFastCardList() {
        AdaPay\AdaPay::$gateWayType = 'page';
        # 初始化对象
        $commonObj = new \AdaPaySdk\AdaPayCommon();

        $params = array(
            "adapay_func_code" => "fast_card.list",
            "adapay_api_version" => "v1",
            'app_id'=> 'app_7d87c043-aae3-4357-9b2c-269349a980d6',
            'token_no'=> '10000067502',
            'member_id'=> 'member_id_test'
        );
        $commonObj->queryAdapayUits($params);
        print("创建快捷支付绑卡对象".$commonObj->isError().'=>'.json_encode($commonObj->result)."\n");
        $this->assertEquals('succeeded', $commonObj->result['status']);
    }
}
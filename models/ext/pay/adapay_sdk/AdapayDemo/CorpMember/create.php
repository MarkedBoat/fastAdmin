<?php
/**
 * AdaPay 创建企业用户
 * author: adapay.com https://docs.adapay.tech/api/04-trade.html
 * Date: 2019/09/17
 */
# 创建企业用户

# 加载SDK需要的文件
include_once  dirname(__FILE__). "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once  dirname(__FILE__). "/../config.php";


# 初始化用户对象类
$corp_member = new \AdaPaySdk\CorpMember();

$file_real_path = realpath('123.zip');
print_r($file_real_path);
$member_params = array(
    # app_id
    "app_id"=> "app_7d87c043-aae3-4357-9b2c-269349a980d6",
    # 商户用户id
    "member_id"=> "vrleo_business_test_".date("YmdHis").rand(1000, 9999),
    # 订单号
    "order_no"=> date("YmdHis").rand(100000, 999999),
    # 企业名称
    "name"=> "上海狮峪智能科技有限公司",
    # 省份
    "prov_code"=> "0031",
    # 地区
    "area_code"=> "3100",
    # 统一社会信用码
    "social_credit_code"=> "91310104MA1FR7RT8G",
    "social_credit_code_expires"=> "20991231",
    # 经营范围
    "business_scope"=> "计算机网络科技、计算机科技、计算机系统集成领域内的技术开发",
    # 法人姓名
    "legal_person"=> "陈修超",
    # 法人身份证号码
    "legal_cert_id"=> "321088198511188530",
    # 法人身份证有效期
    "legal_cert_id_expires"=> "20360704",
    # 法人手机号
    "legal_mp"=> "15618705189",
    # 企业地址
    "address"=> "上海市徐汇区华泾路509号7幢377室",
    # 邮编
    "zip_code"=> "200030",
    # 企业电话
    "telphone"=> "15900934709",
    # 企业邮箱
    "email"=> "feagle@7663.com",
    # 上传附件
    "attach_file"=> new CURLFile($file_real_path),
    # 银行代码
    "bank_code"=> "04020031",
    # 银行账户类型
    "bank_acct_type"=> "1", //1-对公；2-对私，如果需要自动开结算账户，本字段必填
    # 银行卡号
    "card_no"=> "50131000600408855", //如果需要自动开结算账户，本字段必填
    # 银行卡对应的户名
    "card_name"=> "上海狮峪智能科技有限公司", //如果需要自动开结算账户，本字段必填
);
var_dump($member_params);
//$member_params = array(
//    # app_id
//    'app_id'=> 'app_1936c592-56b9-49a2-ad10-309391eea155',
//    # 商户用户id
//    'member_id'=> 'shop_87',
//    # 订单号
//    'order_no'=> '20210831141833192203',
//    # 企业名称
//    'name'=> '抚州市江景贸易有限公司',
//    # 省份
//    'prov_code'=> '0036',
//    # 地区
//    'area_code'=> '3602',
//    # 统一社会信用码
//    'social_credit_code'=> '91361002MA38KW815F',
//    'social_credit_code_expires'=> '20420828',
//    # 经营范围
//    'business_scope'=> '抚州市江景贸易有限公司成立于2019年05月13日，注册地位于江西省抚州市临川区赣东大道68号(钧天.城市旗舰)5幢203室，法定代表人为左志波。经营范',
//    # 法人姓名
//    'legal_person'=> '左志波',
//    # 法人身份证号码
//    'legal_cert_id'=> '362502198902200416',
//    # 法人身份证有效期
//    'legal_cert_id_expires'=> '20380830',
//    # 法人手机号
//    'legal_mp'=> '13807948536',
//    # 企业地址
//    'address'=> '江西省抚州市临川区赣东大道68号（钧天.城市旗舰）5幢203室',
//    # 邮编
//    'zip_code'=> '344000',
//    # 企业电话
//    'telphone'=> '13807948536',
//    # 企业邮箱
//    'email'=> 'fuzhou@miaota.vip',
//    # 上传附件
//    'attach_file'=> new CURLFile($file_real_path),
//    # 银行代码
//    'bank_code'=> '1001',
//    # 银行账户类型
//    'bank_acct_type'=> '1',
//
//    "card_no"=> "36050185015200000424", //如果需要自动开结算账户，本字段必填
//
//    'card_name'=> '中国建设银行股份有限公司抚州紫竹支行'
//);
# 创建企业用户
$corp_member->create($member_params);

# 对创建企业用户结果进行处理
if ($corp_member->isError()){
    //失败处理
    var_dump($corp_member->result);
} else {
    //成功处理
    var_dump($corp_member->result);
}
<?php
# 更新企业用户
# 加载SDK需要的文件
include_once  dirname(__FILE__). "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once  dirname(__FILE__). "/../config.php";


# 初始化用户对象类
$corp_member = new \AdaPaySdk\CorpMember();

$file_real_path = realpath('123.zip');
$member_params = array(
    # app_id
    'app_id'=> 'app_7d87c043-aae3-4357-9b2c-269349a980d6',
    # 商户用户id
    'member_id'=> 'vrleo_business_test_202109171516546362',
    # 订单号
    'order_no'=> '20210917151654520686',
    # 企业名称
    'name'=> '抚州市江景贸易有限公司1',
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
//    'card_name'=> '中国建设银行股份有限公司抚州紫竹支行',
//    'notify_url'=> 'https://www.adapay.tech/'
);
# 创建企业用户
$corp_member->update($member_params);

# 对创建企业用户结果进行处理
if ($corp_member->isError()){
    //失败处理
    var_dump($corp_member->result);
} else {
    //成功处理
    var_dump($corp_member->result);
}
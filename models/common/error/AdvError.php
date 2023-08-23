<?php


namespace models\common\error;


use models\common\sys\Sys;

class AdvError extends \Exception
{
    protected $detail_code = '';
    private   $debug_data;

    const dispatcher_interruption = ['code' => 1, 'detail' => 'dispatcher_interruption', 'msg' => '中断'];

    const data_not_exist       = ['code' => 10001, 'detail' => 'data_not_exist', 'msg' => '数据不存在'];
    const data_error           = ['code' => 10002, 'detail' => 'data_error', 'msg' => '数据错误'];
    const format_param_error   = ['code' => 10003, 'detail' => 'format_param_error', 'msg' => '处理参数错误'];
    const data_record_fail     = ['code' => 10100, 'detail' => 'data_record_fail', 'msg' => '数据记录失败'];
    const data_info_error      = ['code' => 10200, 'detail' => 'data_info_error', 'msg' => '数据信息错误'];
    const data_info_unexpected = ['code' => 10201, 'detail' => 'data_info_unexpected', 'msg' => '数据信息异常，不在预期中'];


    const request_param_error       = ['code' => 20000, 'detail' => 'request_param_error', 'msg' => '处理参数错误'];
    const request_param_empty       = ['code' => 20001, 'detail' => 'request_param_empty', 'msg' => '处理参数错误'];
    const request_param_not_json    = ['code' => 20002, 'detail' => 'request_param_not_json', 'msg' => '处理参数错误'];
    const request_method_deny       = ['code' => 20003, 'detail' => 'request_param_empty', 'msg' => '请求METHOD不支持'];
    const request_param_verify_fail = ['code' => 20004, 'detail' => 'request_param_verify_fail', 'msg' => '参数信息校验失败'];
    const request_timeout           = ['code' => 20005, 'detail' => 'request_timeout', 'msg' => '请求参数超时'];
    const request_sign_error        = ['code' => 20006, 'detail' => 'request_sign_error', 'msg' => '请求参数签名错误'];


    const db_common_error = ['code' => 30000, 'detail' => 'db_common_error', 'msg' => '数据库通用错误'];
    const db_save_error   = ['code' => 30001, 'detail' => 'db_save_error', 'msg' => '保存失败'];


    const res_common_error  = ['code' => 40000, 'detail' => 'res_common_error', 'msg' => '数据错误(尤指查询)'];
    const res_not_exist     = ['code' => 40001, 'detail' => 'res_not_exist', 'msg' => '资源不存在'];
    const res_lock_fail     = ['code' => 40002, 'detail' => 'res_lock_fail', 'msg' => '资源锁定失败'];
    const res_in_locking    = ['code' => 40003, 'detail' => 'res_in_locking', 'msg' => '资源在锁定中，等待解锁'];
    const res_has_locked    = ['code' => 40004, 'detail' => 'res_in_locking', 'msg' => '资源被锁定了，不可用'];
    const res_has_disabled  = ['code' => 40005, 'detail' => 'res_has_disabled', 'msg' => '资源被禁用了，不可用'];
    const res_has_took      = ['code' => 40006, 'detail' => 'res_has_took', 'msg' => '资源已经被消费了'];
    const res_has_delivered = ['code' => 40007, 'detail' => 'res_has_delivered', 'msg' => '已经交付了'];
    const res_reached_limit = ['code' => 40008, 'detail' => 'res_reached_limit', 'msg' => '资源达到了上限'];


    const code_error = ['code' => 90000, 'detail' => 'code_error', 'msg' => '调用错误'];

    const user_error                   = ['code' => 100000, 'detail' => 'user_error', 'msg' => '用户错误'];
    const user_token_error             = ['code' => 101000, 'detail' => 'user_error_token_error', 'msg' => 'user_token_error'];
    const user_token_not_exist         = ['code' => 101001, 'detail' => 'user_error_token_not_exist', 'msg' => '登录信息异常，请重新登录'];
    const user_token_deny              = ['code' => 101001, 'detail' => 'user_error_token_deny', 'msg' => '信息非法，请重新登录'];
    const user_token_expired           = ['code' => 101001, 'detail' => 'user_error_token_expired', 'msg' => '登录超时，请重新登录'];
    const user_money_not_enough_to_pay = ['code' => 102001, 'detail' => 'user_pay_money_not_enough', 'msg' => '用户金额不足以支付'];
    const user_note_not_expired        = ['code' => 103001, 'detail' => 'user_note_not_expired', 'msg' => '用户凭证还未过期'];
    const user_note_has_used           = ['code' => 103002, 'detail' => 'user_note_has_used', 'msg' => '凭证已经被使用过了'];
    const user_note_invalid            = ['code' => 103003, 'detail' => 'user_note_invalid', 'msg' => '用户凭证非法'];
    const user_lasted_note_is_ok       = ['code' => 103002, 'detail' => 'user_lasted_note_is_ok', 'msg' => '您之前的凭证还能使用，请勿无需重新兑换'];
    const user_equipment_not_exist     = ['code' => 103101, 'detail' => 'user_equipment_not_exist', 'msg' => '您没有这个装备'];
    const user_op_err_to_fast          = ['code' => 104101, 'detail' => 'user_op_err_to_fast', 'msg' => '您操作过快，请稍后重试'];

    const partner_not_exist             = ['code' => 110001, 'detail' => 'partner_not_exist', 'msg' => '合作方不存在'];
    const pay_error_create_order_fail   = ['code' => 111001, 'detail' => 'pay_error_create_order_fail', 'msg' => '创建订单失败'];
    const pay_error_create_payment_fail = ['code' => 111002, 'detail' => 'pay_error_create_payment_fail', 'msg' => '创建订单失败'];

    const rbac_error = ['code' => 120000, 'detail' => 'rbac_error', 'msg' => '权限问题'];
    const rbac_deny  = ['code' => 120001, 'detail' => 'rbac_deny', 'msg' => '您没有权限'];


    public function __construct($info, $msg = '', $debug_data = [])
    {
        $this->detail_code = $info['detail'];
        $this->debug_data  = $debug_data;
        $msg               = $msg ? $msg : $info['msg'];
        Sys::app()->getDispatcher()->createInterruptionInfo($info['detail'], $msg, false, $debug_data);
        parent::__construct($msg, 400);
    }

    public static function logError($msg, $data = false)
    {
        Sys::app()->addLog("AdvError:" . $msg . "\n" . ($data === false ? '' : (var_export(is_array($data) ? $data : [$data], true))) . "\n", false, false);//垃圾thinkphp！ Log::error 第二个参数，传array 和 string 都报错
    }

    public function getDetailCode()
    {
        return $this->detail_code;
    }


}
<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\lottery\Lottery;

//用户提现申请
class ActionApplyPay extends GameBaseAction
{

    public function run()
    {
        //throw new AdvError(AdvError::db_common_error,'现在正在审核数据，提现功能暂时关闭，预计到今天上午11时恢复，即时可以正常提现');
        $result = ['code' => '0', 'status' => '400', 'msg' => '获取失败'];
        $type = $this->inputDataBox->getStringNotNull('type');
        $data['user_id'] = $this->user->id;
        $data['order_id'] = strval(time()).strval(rand(10000,99999));//订单号
        $data['description'] = 0;
        $data['status'] = 1;
        $data['type'] = $type;
        $yuan = $this->inputDataBox->getStringNotNull('money');//默认提交多少元
        if(intval($yuan) != $yuan){
            throw new \Exception('提现金额必须是整数',400);
        }
        //判断当前是否有待审核的提现
        $applyList = Sys::app()->db('dev')->setText("select * from bi_user_apply_pay where user_id = :user_id and is_ok = 1 and status = 1")->bindArray(['user_id'=>$data['user_id']])->queryRow();
        if($applyList){
            throw new \Exception('您已有一条待审核申请',400);
        }
        //判断剩余元宝数量
        $gold = Sys::app()->db('dev')->setText("select id,item_amount from bi_user_currency where user_id=:user_id and item_code = 'gold_ingot'")->bindArray(['user_id'=>$this->user->id])->queryRow();

        //根据元宝对现金比例算需要多少元宝
        $rate = Sys::app()->db('dev')->setText("select JSON_EXTRACT(setting,'$.rate[0]') as setting from bi_game_config where item_code = 'rate_gold_ingot'")->queryRow();
        //提现金额测试
        $data['money'] = $yuan;
        //所需元宝数量 为现金*比率
        $goldIngot = $yuan*$rate['setting']*100000000;
        if(!$gold || $gold['item_amount'] - $goldIngot < 0){
            throw new \Exception('剩余元宝数量不足，请重新检查',400);
        }
        $data['gold_ingot'] = $goldIngot;
        //手续费
        $srate = Sys::app()->db('dev')->setText("select JSON_EXTRACT(setting,'$.rate[0]') as setting,JSON_EXTRACT(setting,'$.rate[1]') as litt from bi_game_config where item_code = 'rate_pay_gold_ingot'")->queryRow();
        $commissionrate = ($srate['setting']/pow(10,$srate['litt']))??0.003;
        $data['commission'] = $data['money']*$commissionrate;
        //应到账金额
        $data['receive_money'] = $data['money'] - $data['commission'];
        $where = '(user_id,money,openid,commission,receive_money,description,order_id,type,status,gold_ingot) values (:user_id,:money,:openid,:commission,:receive_money,:description,:order_id,:type,:status,:gold_ingot)';
        if($type == 1){//wechat
            //获取微信绑定数据
            $res = Sys::app()->db('dev')->setText("select * from bi_user_weixin where user_id=:user_id and type = 1")->bindArray(['user_id'=>$data['user_id']])->queryRow();
            if (!$res)
            {
                $result['status'] = 200;
                $result['data']['code'] = 'notbind';
                $result['data']['miniUrl'] = 'https://bee-invasion.oss-cn-hangzhou.aliyuncs.com/icon/wxmini.jpg';
                echo json_encode($result);exit;
            }
            $data['openid'] = $res['openid'];
            $rescode = Sys::app()->db('dev')->setText("insert into bi_user_apply_pay" . $where)->bindArray($data)->execute();
        }elseif ($type == 2){//ali
            $data['openid'] = $this->inputDataBox->getStringNotNull('account');
            $data1['name'] = $this->inputDataBox->getStringNotNull('name');
            $data1['mobile'] = $this->inputDataBox->getStringNotNull('mobile');
            $data['description'] = json_encode($data1,JSON_UNESCAPED_UNICODE);

            if($data['money'] <= 5){//金额小于五元直接打款
                Sys::app()->db('dev')->beginTransaction();

                //扣除相应元宝
                $awardData['user_id'] = $data['user_id'];
                $awardData['item_amount'] = '-'.$goldIngot;
                $awardData['item_code']   = 'gold_ingot';
                //$dogold                   = Lottery::awardRecord($awardData, 0);
                $currency = Sys::app()->db('dev')->setText("update bi_user_currency set item_amount = item_amount-:num where id = :id")->bindArray(['num'=>$goldIngot,'id'=>$gold['id']])->execute();
                $awardData['curr_amount'] = $gold['item_amount'];
                $awardData['expect_amount'] = $gold['item_amount'] - $goldIngot;
                $awardData['src_id'] = time();
                $awardData['src_op_type'] = 2;
                $awardData['src'] = 'order_apply';
                $hiswhere = '(user_id,item_code,item_amount,curr_amount,expect_amount,src_id,src,src_op_type) values (:user_id,:item_code,:item_amount,:curr_amount,:item_amount+:curr_amount,:src_id,:src,:src_op_type)';
                //添加curr操作记录
                $currhis = Sys::app()->db('dev')->setText("insert into bi_user_currency_his".$hiswhere)->bindArray($awardData)->execute();

                //添加提现申请
                $data['status'] = 2;
                $data['update_time'] = date('Y-m-d H:i:s',time());
                $where = '(user_id,money,openid,commission,receive_money,description,order_id,type,status,gold_ingot,update_time) values (:user_id,:money,:openid,:commission,:receive_money,:description,:order_id,:type,:status,:gold_ingot,:update_time)';

                $rescode = Sys::app()->db('dev')->setText("insert into bi_user_apply_pay" . $where)->bindArray($data)->execute();
                if(!$rescode || !$currency || !$currhis){
                    Sys::app()->db('dev')->rollBack();
                    throw  new \Exception('提现申请失败', 400);
                }
                ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $this->user->id], time());


                //                //扣金元宝
//                $awardData['user_id'] = $data['user_id'];
//                //实际扣除金元宝为提现金额*兑换比例
//                $awardData['item_amount'] = $goldIngot;
//                $awardData['item_code'] = 'gold_ingot';
//                $res = Lottery::awardRecord($awardData,0);
//                if(!$res){
//                    throw  new \Exception('金元宝扣除失败，请重新申请！', 400);
//                }

                $url = 'taojin.aiqingyinghang.com/prod-api/third/alipay/alipayFundTransUniTransfer';
                //调用支付宝接口
                $alidata = [
                    'outBizNo'=>$data['order_id'],
                    'transAmount'=>$data['receive_money'],
                    'orderTitle'=>'蜜蜂入侵',
                    'remark'=>'蜜蜂入侵收益',
                    'payeeInfo'=>[
                        'identityType'=>'ALIPAY_LOGON_ID',
                        'identity'=>$data['openid'],
                        'name'=>$data1['name'],
                    ],
                ];
                $alidata = json_encode($alidata,JSON_UNESCAPED_UNICODE);
                $res = json_decode($this->json_post($url,$alidata),true);
                if($res['data']['msg'] != 'Success'){
                    Sys::app()->db('dev')->rollBack();
                    throw new \Exception($res['data']['subMsg'],400);
                }
                Sys::app()->db('dev')->commit();

            }else{
                $rescode = Sys::app()->db('dev')->setText("insert into bi_user_apply_pay" . $where)->bindArray($data)->execute();
            }

        }else{//card
            //获取银行卡号
            $data['openid'] = $this->inputDataBox->getStringNotNull('account');
            $res = $this->checkCard($data['openid']);
            if(!$res){
                throw new \Exception('银行卡号不正确',400);
            }
            $data1['open_bank'] = $this->inputDataBox->getStringNotNull('open_bank');
            $data1['name'] = $this->inputDataBox->getStringNotNull('user_name');
            $data1['bank_name'] = $this->inputDataBox->getStringNotNull('name');
            $data1['mobile'] = $this->inputDataBox->getStringNotNull('mobile');
            $data['description'] = json_encode($data1,JSON_UNESCAPED_UNICODE);
            $rescode = Sys::app()->db('dev')->setText("insert into bi_user_apply_pay" . $where)->bindArray($data)->execute();
        }

        if($rescode){
            $result = ['status'=>200,'code'=>'ok','msg'=>'申请成功，请等待管理员审核'];
            if($type == 2 && $data['money'] <= 5){
                $result = ['status'=>200,'code'=>'ok','msg'=>'提现成功'];
            }
            echo json_encode($result);exit;
        }else{
            throw  new \Exception('提现申请写入失败', 400);
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

    public function json_post($url, $data = NULL)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!$data){
            throw  new \Exception('提现数据为空', 400);
        }
        if(is_array($data))
        {
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            throw  new \Exception($errorno, 400);
        }
        curl_close($curl);
        return $res;

    }

    public function checkCard($card_number){
        $arr_no = str_split($card_number);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }else{
                    $total += $ix;
                }
            }else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if($x == $last_n){
            return 1;
        }else{
            return 0;
        }

    }

}
<?php

namespace modules\bee_invasion\v1\api\game\pay;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;

class ActionPayRecord extends GameBaseAction
{

    //提现记录

    public function run()
    {
        $uid = $this->user->id;
        $p = $_REQUEST['p']??1;
        $num = $_REQUEST['num']??20;
        $statr = ($p-1)*$num;
        $limitStr = $statr.','.$num;
        $res = Sys::app()->db('dev')->setText("select id,money,gold_ingot,status,reason,type,create_time,update_time from bi_user_apply_pay where user_id=:user_id and is_ok = 1 order by id desc limit ".$limitStr)->bindArray(['user_id' =>$uid])->queryAll();
        if($res){
            foreach ($res as &$item)
            {
                $item['gold'] = $item['status']==3?$item['gold_ingot']:'-'.$item['gold_ingot'];
                $item['type'] = $item['type']==1?'微信提现':($item['type']==2?'支付宝提现':'银行卡提现');
                $item['status'] = $item['status']==1?'审核中':($item['status']==2?'提现成功':'提现失败');
            }
        }
        $resule['code'] = 'ok';
        $resule['status'] = 200;
        $resule['data']['list'] = $res??[];
        $resule['data']['page'] = $p;
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $resule['data']['op_flag'] = $op_flag;
        }
        echo json_encode($resule);exit;
    }


}

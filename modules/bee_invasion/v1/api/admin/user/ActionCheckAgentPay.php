<?php

namespace modules\bee_invasion\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionCheckAgentPay extends AdminBaseAction
{
    
    public function run()
    {
        $result = ['code' => '0', 'status' => 400, 'msg' => '修改失败'];
        $id     = $this->inputDataBox->getStringNotNull('id');
        $status     = $this->inputDataBox->getStringNotNull('status');
        if($status != 3){
            throw  new \Exception('审核状态错误', 400);
        }
        $data['id']     = $id;
        $areaRes            = Sys::app()->db('dev')->setText("select * from bi_user_agent_pay_record where id = :id and status = 1 and is_ok = 1")->bindArray($data)->queryRow();
        if (!$areaRes)
        {
            throw  new \Exception('申请记录不存在', 400);
        }
        $data['status']     = $status;
        $res            = Sys::app()->db('dev')->setText("update bi_user_agent_pay_record set status = :status where id = :id")->bindArray($data)->execute();
        if($res){
            $result = ['code' => 'ok', 'status' => 200, 'msg' => '成功'];
        }else{
            $result = ['code' => '0', 'status' => 400, 'msg' => '失败'];
        }

        return $result;
    }
}
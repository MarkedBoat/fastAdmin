<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;


class ActionUserApplyArea extends GameBaseAction
{

    public function run()
    {
        $result = ['code' => '0', 'status' => 400, 'msg' => '失败'];
        $area_code = $this->inputDataBox->getStringNotNull('area_code');
        $uid = $this->user->id;
        $data1['user_id'] = $uid;
        //检查该区域是否已被代理
        $areaArr = Sys::app()->db('dev')->setText("select * from bi_user_bind_area where area_code = :area_code and is_ok = 1")->bindArray(['area_code'=>$area_code])->queryRow();
        if($areaArr){
            throw  new \Exception('该区域已被绑定，无法继续申请', 400);
        }
        //检查该用户是否已绑定其他区域
//        $proArea = Sys::app()->db('dev')->setText("select * from bi_user_profile where user_id = :user_id and is_ok = 1")->bindArray($data1)->queryRow();
//        if($proArea){
//            throw  new \Exception('你已绑定区域，无法申请区域代理', 400);
//        }
        //检测用户是否有待审核的申请
        $areaApply = Sys::app()->db('dev')->setText("select * from bi_user_apply_bind_area where user_id = :user_id and is_ok = 1 and status = 1")->bindArray($data1)->queryRow();
        if($areaApply){
            throw  new \Exception('当前有申请正在审核中，请审核结束后再次提交', 400);
        }
        $data1['area_code'] = $area_code;
        //判断区域编码是否正确
        $areaArr = Sys::app()->db('dev')->setText("select * from bi_area where area_code = :area_code and area_level = 3")->bindArray(['area_code'=>$area_code])->queryRow();
        if (!$areaArr)
        {
            throw  new \Exception('区域编码不正确或不是三级区域，请重新选择', 400);
        }
        $data1['mobile'] = $this->inputDataBox->getStringNotNull('mobile');
        $data1['name'] = $this->inputDataBox->getStringNotNull('name');
        $arr                = Sys::app()->db('dev')->setText("insert into bi_user_apply_bind_area (user_id,area_code,mobile,name) values (:user_id,:area_code,:mobile,:name) ")->bindArray($data1)->execute();
        if($arr){
            $result = ['code' => '0', 'status' => 200, 'data'=>['msg' => '申请成功']];
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

}
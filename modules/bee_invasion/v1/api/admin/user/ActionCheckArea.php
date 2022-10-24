<?php

namespace modules\bee_invasion\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionCheckArea extends AdminBaseAction
{
    
    public function run()
    {
        $result = ['code' => '0', 'status' => 400, 'msg' => '修改失败'];
        $id     = $this->inputDataBox->getStringNotNull('id');
        $data['id']     = $id;
        $areaRes            = Sys::app()->db('dev')->setText("select * from bi_user_apply_bind_area where id = :id and status = 1 and is_ok = 1")->bindArray($data)->queryRow();
        if (!$areaRes)
        {
            throw  new \Exception('申请记录不存在', 400);
        }
        $area_code = $areaRes['area_code'];
        $data['status'] = $this->inputDataBox->getStringNotNull('status');
        if($data['status'] == 2){
            //获取已绑定其他区域
            $areaArr = Sys::app()->db('dev')->setText("select * from bi_user_bind_area where is_ok = 1 and area_code = :area_code")->bindArray(['area_code'=>$area_code])->queryRow();
            if($areaArr){
                throw  new \Exception('该地区已经绑定代理人', 400);
            }
        }

        Sys::app()->db('dev')->beginTransaction();
        $res            = Sys::app()->db('dev')->setText("update bi_user_apply_bind_area set status = :status where id = :id")->bindArray($data)->execute();
        if($data['status'] == 2 && $res){
            $data1['user_id'] = $areaRes['user_id'];
            $data1['area_code'] = $area_code;
            //绑定表添加数据
            $res                = Sys::app()->db('dev')->setText("insert into bi_user_bind_area (user_id,area_code) values (:user_id,:area_code) ")->bindArray($data1)->execute();
        }
        if($res){
            Sys::app()->db('dev')->commit();
            $result = ['code' => 'ok', 'status' => 200, 'msg' => '成功'];
        }else{
            Sys::app()->db('dev')->rollBack();
        }

        return $result;
    }
}
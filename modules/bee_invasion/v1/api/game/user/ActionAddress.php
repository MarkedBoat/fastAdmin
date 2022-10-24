<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use modules\bee_invasion\v1\api\game\GameBaseAction;


class ActionAddress extends GameBaseAction
{


    public function run()
    {

        //$result = ['code' => 0, 'status'=>400,'msg' => '失败'];
        $id = $this->inputDataBox->tryGetString('id');
        $address = $this->inputDataBox->getStringNotNull('address');
        $name = $this->inputDataBox->getStringNotNull('name');
        $mobile = $this->inputDataBox->getStringNotNull('mobile');
        $data['area_code'] = $this->inputDataBox->getStringNotNull('area_code');
        $area = Sys::app()->db('dev')->setText("SELECT * FROM `bi_area` where area_code =:area_code")->bindArray($data)->queryRow();
        if(!$area){
            throw  new \Exception('该区域编码不存在', 400);
        }
        $data['address']    = urldecode($address);
        $data['mobile'] = $mobile;
        $data['name'] = $name;
        if($id){
            $data['id'] = $id;
            //编辑
            $arr                = Sys::app()->db('dev')->setText("update bi_user_profile set address = :address,area_code = :area_code,mobile = :mobile,name = :name where id = :id ")->bindArray($data)->execute();
        }else{
            $data['user_id']    = $this->user->id;
            //新加
            $where              = '(user_id,area_code,address,mobile,name) values (:user_id,:area_code,:address,:mobile,:name)';
            $arr                = Sys::app()->db('dev')->setText("insert into bi_user_profile" . $where)->bindArray($data)->execute();
        }
        $result = ['code' => 'ok', 'status' => '200','msg'=>'成功'];
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;

    }

}
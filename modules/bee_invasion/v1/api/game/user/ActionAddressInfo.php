<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;


class ActionAddressInfo extends GameBaseAction
{

    public function run()
    {
        $data['user_id']    = $this->user->id;
        $arr                = Sys::app()->db('dev')->setText("select id,area_code,address,mobile,name from bi_user_profile where user_id = :user_id")->bindArray($data)->queryRow();
        if($arr){
            $name = Sys::app()->db('dev')->setText("SELECT concat(p.area_name,c.area_name,a.area_name) as address FROM `bi_area` a left join bi_area p on p.area_code = a.province_code left join bi_area c on c.area_code = a.city_code where a.area_code = :area_code")->bindArray(['area_code'=>$arr['area_code']])->queryRow();
            $arr['area'] = $name['address'];
        }
        $result = ['code' => 'ok', 'status' => '200','msg'=>'成功','data'=>$arr?:null];
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;

    }

}
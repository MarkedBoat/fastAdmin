<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;


class ActionUserAreaList extends GameBaseAction
{

    public function run()
    {
        $result = ['code' => 'ok', 'status' => 200, 'msg' => '成功','data'=>[]];
        //获取已绑定其他区域
        $areaArr = Sys::app()->db('dev')->setText("select area_code from bi_user_bind_area where is_ok = 1")->queryAll();
        if($areaArr){
            $result['data']['list'] = $areaArr;
        }else{
            $result['data']['list'] = [];
        }

        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

}
<?php

namespace modules\bee_invasion\v1\api\admin\user;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;

class ActionUserAgentPayList extends AdminBaseAction
{

    public function run()
    {
        $result = ['code' => 'ok', 'status' => 200, 'msg' => '成功','data'=>[]];
        //获取申请列表
        $areaArr = Sys::app()->db('dev')->setText("select * from bi_user_agent_pay_record where is_ok = 1")->queryAll();
        if($areaArr){
            foreach ($areaArr as &$item)
            {
                $item['status'] = $item['status']==1?'待审核':($item['status']==2?'已通过':'已拒绝');
            }
        }
        return $areaArr;
    }
}
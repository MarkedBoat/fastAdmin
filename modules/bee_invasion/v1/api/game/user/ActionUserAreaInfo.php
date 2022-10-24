<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\ActionTool;
use modules\bee_invasion\v1\api\game\GameBaseAction;


class ActionUserAreaInfo extends GameBaseAction
{

    public function run()
    {
        $result = ['code' => 'ok', 'status' => 200, 'msg' => '暂无绑定信息','data'=>['list'=>[],'countNum'=>0]];
        $uid = $this->user->id;
        $data1['user_id'] = $uid;
        $p = $_REQUEST['p']??1;
        $num = $_REQUEST['num']??9;
        $statr = ($p-1)*$num;
        $limitStr = $statr.','.$num;
        //检查该用户是否已绑定其他区域
        $areaArr = Sys::app()->db('dev')->setText("select area_code from bi_user_bind_area where user_id = :user_id and is_ok = 1 order by id desc")->bindArray($data1)->queryAll();
        if($areaArr){
//            $tool = new ActionTool();
//            foreach ($areaArr as $key => $val){
//                $areaArr[$key]['aname'] = $tool->getAreaName($val['area_code']);
//            }
            $result['data']['countNum'] = count($areaArr);
            $areaArr = array_slice($areaArr,$statr,$num);
            $result['msg'] = '成功';
            $result['data']['list'] = $areaArr;
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

}
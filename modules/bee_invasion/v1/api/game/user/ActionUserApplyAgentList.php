<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\user\User;


class ActionUserApplyAgentList extends GameBaseAction
{

    public function run()
    {
        $result = ['code' => 'ok', 'status' => 200, 'msg' => '暂无申请信息','data'=>['list'=>[]]];
        $uid = $this->user->id;
        $data1['user_id'] = $uid;
        $p = $_REQUEST['p']??1;
        $num = $_REQUEST['num']??9;
        $statr = ($p-1)*$num;
        $limitStr = $statr.','.$num;
        //获取用户申请记录
        $areaArr = Sys::app()->db('dev')->setText("select user_id,area_code,name,mobile,create_time,case when status = 1 then '审核中' when status = 2 then '已通过' else '已拒绝' end as status from bi_user_apply_bind_area where user_id = :user_id and is_ok = 1 order by id desc limit ".$limitStr)->bindArray($data1)->queryAll();
        if($areaArr){
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
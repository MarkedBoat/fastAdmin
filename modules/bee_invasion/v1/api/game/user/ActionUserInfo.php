<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\model\user\User;


class ActionUserInfo extends GameBaseAction
{

    public function run()
    {
        $result = ['code' => '0', 'status' => 400, 'msg' => '获取失败'];
        $data['user_id'] = $this->user->id;
        $res = Sys::app()->db('dev')->setText("select id,nickname,mobile,avatar,sex,email,open_id as open_code from bi_user where id=:user_id")->bindArray($data)->queryRow();
        if ($res)
        {
            //$res['id'] = strlen($res['id'])<7?substr(strval($res['id']+1000000),1,6):$res['id'];
            //$res['open_code'] = base_convert($res['open_code'],10,36);
            $res['open_code'] = User::trueId2OpenCode($res['id']);
            $res['sex'] = $res['sex'] == 1 ? '男': '女';
            $res['area_code'] = $res['agent_code'] = '';
            //获取用户绑定区域信息
            $areaArr = Sys::app()->db('dev')->setText("select area_code from bi_user_profile where user_id = :user_id and is_ok = 1")->bindArray($data)->queryRow();
            if($areaArr){
                $res['area_code'] = $areaArr['area_code'];
            }
            //获取用户代理人信息
            $checkArr = Sys::app()->db('dev')->setText("select area_code from bi_user_apply_bind_area where user_id = :user_id and is_ok = 1 and status < 3")->bindArray($data)->queryRow();
            if($checkArr){
                $res['agent_code'] = $checkArr['area_code'];
            }
            $result = ['code' => 'ok', 'status' => '200', 'msg' => '获取成功','data'=>$res];
        }
        $op_flag = $this->inputDataBox->tryGetString('op_flag');
        if($op_flag){
            $result['data']['op_flag'] = $op_flag;
        }
        echo json_encode($result);exit;
    }

}
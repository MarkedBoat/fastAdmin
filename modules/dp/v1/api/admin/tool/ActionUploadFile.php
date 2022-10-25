<?php

namespace modules\bee_invasion\v1\api\admin\tool;

use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\tool\Tool;


class ActionUploadFile extends AdminBaseAction
{


    public function run()
    {
        $path = $this->inputDataBox->tryGetString('path')?:'icon';
        $res = Tool::uploadFile();
        if($res['status'] == 200){
            $res['data']['path'] = $path;
            $res = Tool::uploadOssFile($res['data']);
            if($res){
                exit(json_encode( array( 'status'=>'200','info'=>'上传成功','data'=>$res)));
            }
        }else{
            exit(json_encode($res));
        }
    }

    public function getAreaPro(){
        $items = Sys::app()->db('dev')->setText("select area_code,area_name,if(area_level=1,0,if(area_level=2,province_code,city_code)) parent_id from dp_area  union all
select concat(area_code,'x') area_code,'其他' area_name ,city_code parent_id from dp_area where area_level=2 ORDER BY area_code")->queryAll();


        $tree = []; //格式化好的树
        $items = array_column($items, null, 'area_code');
        if(empty($items)){
            return $tree;
        }
        foreach ($items as $item){
            if (isset($items[$item['parent_id']])){
                $items[$item['parent_id']]['son'][] = &$items[$item['area_code']];
            }else{
                $tree[] = &$items[$item['area_code']];
            }
        }
        //转成json文件 再上传oss
        $json = json_encode($tree,JSON_UNESCAPED_UNICODE);
        echo $json;exit;
    }
}
<?php

namespace modules\bee_invasion\v1\api\admin\tool;

use models\common\sys\Sys;
use models\ext\src\Upload;
use models\ext\tool\UploadFile;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\model\tool\Tool;


class ActionTool extends AdminBaseAction
{


    public function run()
    {
        $cacheName = $this->inputDataBox->getStringNotNull('key');
        $type = $_REQUEST['type']??0;
        if($type == 1){
            $value = $_REQUEST['value']??1;
            $res = Sys::app()->redis('cache')->set($cacheName,$value,86400);
        }elseif($type == 2){
            $res = Sys::app()->redis('cache')->del($cacheName);
        }else{
            $res = Sys::app()->redis('cache')->get($cacheName);
            return $res;
        }
        if($res){
            return 1;
        }else{
            return 0;
        }
    }
//上传oss图片
    public function test(){
        //$res = Upload::uploadFile();
        $res = Tool::uploadFile();
        if($res['status'] == 200){
            $res = Tool::uploadOssFile($res['data']);
            if($res){
                exit(json_encode( array( 'status'=>'200','info'=>'上传成功','data'=>$res)));
            }
        }
    }

    public function getArea(){
        $items = Sys::app()->db('dev')->setText("select * from dp_area")->queryAll();
        $tree = []; //格式化好的树
        $items = array_column($items, null, 'id');
        if(empty($items)){
            return $tree;
        }
        foreach ($items as $item){
            if (isset($items[$item['pid']])){
                $items[$item['pid']]['son'][] = &$items[$item['id']];
            }else{
                $tree[] = &$items[$item['id']];
            }
        }
        //转成json文件 再上传oss
        $json = json_encode($tree,JSON_UNESCAPED_UNICODE);

        $fileUrl = 'data/upload';
        if(!file_exists($fileUrl)){
            mkdir($fileUrl,0777,true);
        }
        file_put_contents($fileUrl.'/area.json',$json);
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
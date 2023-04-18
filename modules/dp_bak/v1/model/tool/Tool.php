<?php

namespace modules\dp\v1\model\tool;

use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\src\Upload;
use models\ext\tool\UploadFile;

class Tool
{
    //上传文件到服务器
    public static function uploadFile(){
        $upload = new UploadFile();

        $upload->allowExts          = array('jpg','png','jpeg');
        $upload->saveRule           = time().'_'.mt_rand(1000,9999);
        $upload->savePath           = $_SERVER['DOCUMENT_ROOT'].'/log/file/image/';
        $upload->maxSize            = 8388608;

        if(!is_dir($upload->savePath)){
            mkdir($upload->savePath,0777,true);
        }
        if( !$upload->upload() ){
            return array( 'status'=>'400','info'=>$upload->getErrorMsg(),'data'=>false );
        }else{
            $info = $upload->getUploadFileInfo();
            $data['savepath'] = $upload->savePath;
            $data['savename'] = $info['0']['savename'];
            return array( 'status'=>'200','info'=>'上传成功','data'=>$data);
        }
    }

    public static function uploadOssFile($data){
        $res = Upload::uploadFile($data['savename'],$data['savepath'],$data['path']);
        if($res){
            //删除服务器文件
            unlink($data['savepath'].$data['savename']);
        }
        return $res;
    }

    /**
     * +----------------------------------------------------------
     * 生成随机字符串
     * +----------------------------------------------------------
     * @param int $length 要生成的随机字符串长度
     * @param int $type 随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符；5，数字+大写字母；
     * +----------------------------------------------------------
     * @return string
     * +----------------------------------------------------------
     */
    public function randCode($length = 5, $type = 0)
    {
        $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0)
        {
            array_pop($arr);
            $string = implode("", $arr);
        }
        elseif ($type == "-1")
        {
            $string = implode("", $arr);
        }
        elseif ($type == "5")
        {
            $array  = array($arr[1], $arr[3]);
            $string = implode("", $array);
        }
        else
        {
            $string = $arr[$type];
        }
        //sprintf('%x',crc32(microtime()))
        $count = strlen($string) - 1;
        $code  = '';
        for ($i = 0; $i < $length; $i++)
        {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }




    
}
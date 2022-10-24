<?php
namespace models\ext\src;

use OSS\OssClient;
use OSS\Core\OssException;

include_once  dirname(__FILE__). "/../autoload.php";
//require_once '/autoloader.php';

class Upload {
    //filename 上传文件名称 filepath 文件保存路径
    public static function uploadFile($filename,$filepath,$path='icon'){
        // 阿里云账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM用户进行API访问或日常运维，请登录RAM控制台创建RAM用户。
        $accessKeyId = "LTAI5tDPvTe2VHXkG4F511zk";
        $accessKeySecret = "Te40Lc4cEBGl1gpU61rA6HwoHNkxiX";
        $endpoint = "https://oss-cn-hangzhou.aliyuncs.com";
        $bucket= "bee-invasion";
        //$object = "icon/".$filename;
        $object = $path.'/'.$filename;
        $filePath = $filepath.$filename;//"D:\phpstudy_pro\WWW\aliyunsdk\wxmini.jpg";
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $ossClient->uploadFile($bucket, $object, $filePath);
        } catch(OssException $e) {
//            printf(__FUNCTION__ . ": FAILED\n");
//            printf($e->getMessage() . "\n");
            unlink($filePath);
            throw  new \Exception($e->getMessage(), 400);
        }
        return 'https://bee-invasion.oss-cn-hangzhou.aliyuncs.com/'.$object;
    }

}

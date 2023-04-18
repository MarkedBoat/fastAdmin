<?php

namespace modules\dp\v1\api\admin\file;

use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\dp\v1\api\admin\AdminBaseAction;

class ActionUpload extends AdminBaseAction
{


    public function run()
    {
        //   $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;

        //  var_dump($_FILES, $_POST);
        if (!isset($_FILES['file']))
        {
            throw new AdvError(AdvError::request_param_error, '没有上传的文件');
        }

        $relat_div = '/static/upload/' . date('Y/m');;
        $dir = __ROOT_DIR__ . $relat_div;
        if (!file_exists($dir))
        {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($dir))
        {
            throw new AdvError(AdvError::code_error, "不能创建目录 {$dir}");
        }

        $md5       = md5(file_get_contents($_FILES['file']['tmp_name']));
        $file_info = pathinfo($_FILES['file']['name']);
        $ext       = $file_info['extension'];
        $dst_file  = "{$dir}/{$md5}.{$ext}";
        $res       = move_uploaded_file($_FILES['file']['tmp_name'], $dst_file);
        // var_dump($dst_file, $res);
        return [
            'file_info' => $file_info,
            'url'       => "{$relat_div}/{$md5}.{$ext}",
            'filename'  => $dst_file
        ];
    }


}
<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\AdminDao;

class ActionUsers extends AdminBaseAction
{
    public function run()
    {
        $data['is_ok'] = Opt::isOk;
        $where = 'is_ok = :is_ok';
        $name = $this->inputDataBox->tryGetString('keyword');
        if($name){
            $where .= " and (LOCATE(:keyword,real_name) != 0 or id = :keyword)";
            $data['keyword'] = $name;
        }
        return Sys::app()->db('dp')->setText("select * from bg_admin where ".$where)->bindArray($data)->queryAll();
    }


}
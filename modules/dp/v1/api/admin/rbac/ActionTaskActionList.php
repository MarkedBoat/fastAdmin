<?php

namespace modules\dp\v1\api\admin\rbac;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;

class ActionTaskActionList extends AdminBaseAction
{
    public function run()
    {
        $data['id'] = $this->inputDataBox->getStringNotNull('id');

        return Sys::app()->db('dp')->setText("select r_t.id, r.task_name,t.action_name,t.action_code,'task_action' as tname from bg_rbac_task r left join bg_rbac_task_action r_t on r.id = r_t.task_id left join bg_rbac_action t on t.id = r_t.action_id  where r.id = :id and r.is_ok = 1 and r_t.is_ok = 1 and t.is_ok = 1")->bindArray($data)->queryAll();
    }

}
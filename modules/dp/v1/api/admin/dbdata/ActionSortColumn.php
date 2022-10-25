<?php

namespace modules\bee_invasion\v1\api\admin\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\dbdata\DbColumn;
use modules\bee_invasion\v1\model\admin\dbdata\DbTable;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;


class ActionSortColumn extends AdminBaseAction
{
    public function run()
    {
        $ids = $this->inputDataBox->getArrayNotNull('ids');
        $ids = array_reverse($ids);

        $m   = DbColumn::model();
        $tn  = $m->getTableName();
        $cmd = $m->getDbConnect()->setText("update {$tn} set column_sn=:sn where id=:id");
        $res = [];
        foreach ($ids as $i => $id)
        {
            $res[] = [
                'id'  => $id,
                'sn'  => ($i + 1) * 100,
                'res' => $cmd->bindArray([':id' => $id, ':sn' => ($i + 1) * 100])->execute()
            ];
        }
        return ['res' => $res];
    }


}
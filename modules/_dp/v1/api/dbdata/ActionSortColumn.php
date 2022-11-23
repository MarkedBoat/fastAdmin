<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


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
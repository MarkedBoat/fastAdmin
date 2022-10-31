<?php

namespace modules\dp\v1\api\admin\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;


class ActionFillTable extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;

        $rows     = DbTable::model()->getDbConnect()->setText("SELECT `table_schema`,`table_name`,table_comment FROM information_schema.Tables WHERE table_schema = 'dev_bg';")->queryAll();
        $tn       = DbTable::model()->getTableName();
        $sqls     = [];
        $date_now = date('Y-m-d H:i:s');
        $bind     = [];
        foreach ($rows as $i => $row)
        {
            $bind[":db_{$i}"]    = $row['TABLE_SCHEMA'];
            $bind[":tn_{$i}"]    = $row['TABLE_NAME'];
            $bind[":title_{$i}"] = $row['TABLE_COMMENT'];
            $sqls[]              = "insert ignore into {$tn} set db_name=:db_{$i},table_name=:tn_{$i},title=:title_{$i},remark=:title_{$i} on duplicate key update is_ok=1";
        }
        $res = DbTable::model()->getDbConnect()->setText(join(';', $sqls))->bindArray($bind)->execute();


    }


}
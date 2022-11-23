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


class ActionFillTable extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;

        $rows = DbTable::model()->getDbConnect()->setText("SELECT `table_schema`,`table_name`,table_comment FROM information_schema.Tables WHERE table_schema = 'dev_bg';")->queryAll();
        $tn   = DbTable::model()->getTableName();
        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$tn]))
        {
            $tn = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$tn];
        }
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
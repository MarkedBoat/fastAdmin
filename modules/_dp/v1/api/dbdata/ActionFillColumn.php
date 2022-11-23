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


class ActionFillColumn extends AdminBaseAction
{
    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;
        // $db                     = $this->inputDataBox->getStringNotNull('db');
        $db = 'dev_bg';
        $tn = $this->inputDataBox->getStringNotNull('table');

        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$tn]))
        {
            $tn = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$tn];
        }

        $tn_table    = DbTable::model()->getTableName();
        $tn_col      = DbColumn::model()->getTableName();
        $rows        = DbTable::model()->getDbConnect()->setText("show full columns from {$tn};")->queryAll();
        $sqls        = [];
        $update_sqls = [];
        $bind        = [':db_name' => $db, ':tn' => $tn];
        $table       = DbTable::model()->getDbConnect()->setText("SELECT id,column_name FROM {$tn_col} WHERE db_name=:db_name and table_name=:tn AND is_ok=1")->bindArray($bind)->queryAll();
        $ks          = [];
        $pk          = '';
        foreach ($table as $row)
            $ks[$row['column_name']] = $row['id'];
        foreach ($rows as $i => $row)
        {
            $bind[":column_name_{$i}"]     = $row['Field'];
            $bind[":title_{$i}"]           = $row['Comment'];
            $bind[":out_datatype_{$i}"]    = $row['Type'] === 'json' ? 'json' : (strstr($row['Type'], 'int(') ? 'int' : 'string');
            $ar                            = explode('(', $row['Type']);
            $bind[":db_datatype_{$i}"]     = $ar[0];
            $bind[":db_datatype_len_{$i}"] = isset($ar[1]) ? intval(str_replace([')'], '', $ar[1])) : 0;
            $bind[":index_key_{$i}"]       = $row['Key'];
            $bind[":default_val_{$i}"]     = $row['Default'];
            $sqls[]                        = "insert ignore into {$tn_col} set db_name=:db_name,table_name=:tn,column_name=:column_name_{$i},title=:title_{$i},remark=:title_{$i},out_datatype=:out_datatype_{$i},db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},val_range='[]' on duplicate key update db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},is_ok=1";
            if (isset($ks[$row['Field']]))
                unset($ks[$row['Field']]);
            if ($pk === '' && $row['Key'] === 'PRI')
            {
                $pk            = $row['Field'];
                $update_sqls[] = "update {$tn_table} set pk_key='{$pk}' where db_name='{$db}' and table_name='{$tn}'";
            }
        }
        if (count($ks))
            foreach ($ks as $key => $id)
                $update_sqls[] = "update {$tn_col} set is_ok=2 where id='{$id}'";

        //  return   $this->returnSuccess( $this->inputparam::getComapareSqlParamRes(join(';', $sqls),$bind));
        $try_insert_cnt = DbTable::model()->getDbConnect()->setText(join(';', $sqls))->bindArray($bind)->execute();
        $update_cnt     = 0;
        foreach ($update_sqls as $update_sql)
        {
            $update_cnt += DbTable::model()->getDbConnect()->setText($update_sql)->execute();
        }

        echo "\ntry_insert:{$try_insert_cnt}\nupdate:{$update_cnt}\n\n";

        var_dump($rows);

        echo "\nSUCCESS\n";


    }


}
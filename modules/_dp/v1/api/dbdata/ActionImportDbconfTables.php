<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;

ini_set('max_execution_time', 0);

class ActionImportDbconfTables extends AdminBaseAction
{
    public function run()
    {

        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;

        $is_super = in_array('super_admin', $this->user->role_codes, true);
        if (!$is_super)
        {
            die('super_admin only ');
        }

        $db_code = $this->inputDataBox->getStringNotNull('dbconf_code');
        if ($db_code === '$sys' || $db_code === 'fast_bg')
        {
            $db_name     = 'fast_bg';
            $db_cnn      = DbTable::model()->getDbConnect();
            $dbconf_name = 'fast_bg';
        }
        else
        {
            $conf_model  = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
            $dbconf_name = $db_code;
            $db_name     = $conf_model->conf_dbname;
            $db_cnn      = $conf_model->getConfDbConnect();

        }
        //$dbconf_name = 'fast_bg';
        // $db_name     = 'fast_bg';
        $rows        = $db_cnn->setText("SELECT `table_schema`,`table_name`,table_comment FROM information_schema.Tables WHERE table_schema = '{$db_name}';")->queryAll();
        $tn          = DbTable::model()->getTableName();
        $sqls        = [];
        $date_now    = date('Y-m-d H:i:s');
        $bind        = [];
        $table_names = [];
        foreach ($rows as $i => $row)
        {
            $table_names[]       = $row['TABLE_NAME'];
            $bind[":db_{$i}"]    = $row['TABLE_SCHEMA'];
            $bind[":tn_{$i}"]    = $row['TABLE_NAME'];
            $bind[":title_{$i}"] = $row['TABLE_COMMENT'];
            $sqls[]              = "insert ignore into {$tn} set dbconf_name=:db_{$i},table_name=:tn_{$i},title=:title_{$i},remark=:title_{$i} on duplicate key update is_ok=1";
        }
        $res = DbTable::model()->getDbConnect()->setText(join(';', $sqls))->bindArray($bind)->execute();

        $tn_col   = DbColumn::model()->getTableName();
        $tn_table = DbTable::model()->getTableName();
        $i        = 0;
        $cnt      = count($table_names);
        foreach ($table_names as $tn)
        {
            $i++;
            echo "\n{$i}/{$cnt} try_insert:{$tn}\n";
            $rows        = $db_cnn->setText("show full columns from {$tn};")->queryAll();
            $sqls        = [];
            $update_sqls = [];
            $bind        = [':dbconf_name' => $dbconf_name, ':tn' => $tn];
            $table       = DbTable::model()->getDbConnect()->setText("SELECT id,column_name FROM {$tn_col} WHERE dbconf_name=:dbconf_name and table_name=:tn AND is_ok=1")->bindArray($bind)->queryAll();
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
                $sqls[]                        = "insert ignore into {$tn_col} set dbconf_name=:dbconf_name,table_name=:tn,column_name=:column_name_{$i},title=:title_{$i},remark=:title_{$i},out_datatype=:out_datatype_{$i},db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},val_items='[]' on duplicate key update db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},is_ok=1";
                if (isset($ks[$row['Field']]))
                    unset($ks[$row['Field']]);
                if ($pk === '' && $row['Key'] === 'PRI')
                {
                    $pk            = $row['Field'];
                    $update_sqls[] = "update {$tn_table} set pk_key='{$pk}' where dbconf_name='{$db_name}' and table_name='{$tn}'";
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
        }


    }


}
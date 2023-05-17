<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\db\MysqlPdo;
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

        if (0)
        {
            $db = new MysqlPdo("mysql:host=www.markedboat.com;port=3306;dbname=test;charset=utf8mb4", "u20230411", "@Bc123", array_merge([], [
                //  \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                \PDO::ATTR_TIMEOUT          => 10,
                \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT       => true,
                \PDO::ATTR_EMULATE_PREPARES => true
            ]));
            // $db->setText('SET NAMES utf8mb4;')->execute();
            $res = $db->setText('select * from test.kz_mark limit 2;')->queryAll();
            var_dump($res);
            die;
        }


        $is_super = in_array('_super_admin', $this->user->role_codes, true);
        if (!$is_super)
        {
            die('_super_admin only ');
        }

        $db_code    = $this->inputDataBox->getStringNotNull('dbconf_code');
        $is_recover = ($this->inputDataBox->tryGetString('is_force') === 'yes');

        if ($db_code === '_sys_')
        {
            $dbconf_name = '_sys_';
            $db_name     = 'kl_dev_bg';
            $db_cnn      = Sys::app()->db('_sys_');
        }
        else
        {
            $conf_model  = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
            $dbconf_name = $db_code;
            $db_name     = $conf_model->conf_dbname;
            $db_cnn      = $conf_model->getConfDbConnect();
        }


        $rows        = $db_cnn->setText("SELECT `table_schema`,`table_name` 'TABLE_NAME',table_comment 'TABLE_COMMENT' FROM information_schema.Tables WHERE table_schema = '{$db_name}';")->queryAll();
        $tn          = DbTable::model()->getTableName();
        $sqls        = [];
        $date_now    = date('Y-m-d H:i:s');
        $bind        = [];
        $table_names = [];
        foreach ($rows as $i => $row)
        {
            $table_names[] = $row['TABLE_NAME'];
            //   $bind[":db_{$i}"]    = $row['TABLE_SCHEMA'];
            $bind[":db_{$i}"]    = $dbconf_name;
            $bind[":tn_{$i}"]    = $row['TABLE_NAME'];
            $bind[":title_{$i}"] = $row['TABLE_COMMENT'];
            $recover_sql         = $is_recover ? ",title=:title_{$i},remark=:title_{$i}" : '';
            $sqls[]              = "insert ignore into {$tn} set dbconf_name=:db_{$i},table_name=:tn_{$i},title=:title_{$i},remark=:title_{$i},default_opts='{\"struct\":[],\"struct_code\":\"dbdataTableDefaultOpts\"}' on duplicate key update is_ok=1{$recover_sql}";
        }
        $res = DbTable::model()->getDbConnect()->setText(join(';', $sqls))->bindArray($bind)->execute();
        var_dump($sqls, $bind);

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
                $recover_sql                   = $is_recover ? ",title=:title_{$i},remark=:title_{$i}" : '';
                $sqls[]                        = "insert ignore into {$tn_col} set dbconf_name=:dbconf_name,table_name=:tn,column_name=:column_name_{$i},title=:title_{$i},remark=:title_{$i},out_datatype=:out_datatype_{$i},db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},val_items='[]',default_opts='{}' on duplicate key update db_datatype=:db_datatype_{$i},db_datatype_len=:db_datatype_len_{$i},index_key=:index_key_{$i},default_val=:default_val_{$i},is_ok=1{$recover_sql}";
                if (isset($ks[$row['Field']]))
                    unset($ks[$row['Field']]);
                if ($pk === '' && $row['Key'] === 'PRI')
                {
                    $pk            = $row['Field'];
                    $update_sqls[] = "update {$tn_table} set pk_key='{$pk}' where dbconf_name='{$db_code}' and table_name='{$tn}'";
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
        $tn_db = DbDbConf::$tableName;

        $update_sqls = [
            "update {$tn_db} set access_role_codes='[]' where access_role_codes is null",
            "update {$tn_table} set default_opts='{\"struct\":[],\"struct_code\":\"dbdataTableDefaultOpts\"}' where default_opts is null",
            "update {$tn_table} set access_role_codes='[]' where access_role_codes is null",
            "update {$tn_table} set access_insert_role_codes='[]' where access_insert_role_codes is null",
            "update {$tn_col} set default_opts='{}' where default_opts is null",
            "update {$tn_col} set access_select_role_codes='[]' where access_select_role_codes is null",
            "update {$tn_col} set access_update_role_codes='[]' where access_update_role_codes is null",
            "update {$tn_col} set val_items='[]' where val_items is null",
            "update {$tn_col} set val_items_link='{}' where val_items_link is null",
        ];

        foreach ($update_sqls as $update_sql)
        {
            $res = intval(DbTable::model()->getDbConnect()->setText($update_sql)->execute());
            echo "\n{$update_sql}\n{$res}\n";
        }


    }


}
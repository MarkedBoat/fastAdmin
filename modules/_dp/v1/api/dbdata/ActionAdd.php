<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\dbdata\DbOpLogDao;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionAdd extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db_code     = $this->inputDataBox->getStringNotNull('dbconf_name');
        $table_name  = $this->inputDataBox->getStringNotNull('table_name');
        $attr        = $this->inputDataBox->tryGetArray('attr');
        $update_attr = $this->inputDataBox->tryGetArray('update_attr');


        $is_super      = in_array('_super_admin', $this->user->role_codes, true);
        $db_conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code, 'is_ok' => Opt::YES]);
        if ($is_super === false && $db_conf_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "无权访问Db:[{$db_code}]", false);
        }
        $table_name       = DbTable::replaceFakeTableName($table_name);
        $table_conf_model = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $table_name, 'is_ok' => Opt::YES]);
        if ($is_super === false && $table_conf_model->checkInsertAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "无权对表新增:[{$db_code}.{$table_name}]", false);
        }

        $dbconf_name  = $db_code;
        $user_roles   = $this->user->role_codes;
        $user_roles[] = '*';
        $db_table     = DbTable::model()->setBizTableModel($table_conf_model)->setTable($dbconf_name, $table_name);
        $table_model  = $db_table->getBizTableInfo();
        $errors       = [];
        $bind         = [];
        $select_bind  = [];
        $sets         = [];
        $update       = [];
        $pk           = '';
        if ($is_super || array_intersect($user_roles, $table_model->access_insert_role_codes))
        {
            $column_models = $db_table->getBizTableColumns();
            Sys::app()->addLog($column_models);
            foreach ($column_models as $column_model)
            {
                if ($column_model->index_key === 'PRI')
                {
                    if (isset($attr[$column_model->column_name]))
                    {
                        $errors[] = "不得含有pk";
                        continue;
                    }
                    $pk = $column_model->column_name;

                }
                if ($column_model->column_name === 'create_by')
                {
                    $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                    $bind[":{$column_model->column_name}"] = $this->user->id;
                    continue;
                }

                if (isset($attr[$column_model->column_name]))
                {
                    if ($is_super === false && $column_model->checkUpdateAccess($this->user) === false)
                    {
                        $errors[] = "无权操作:{$column_model->column_name}";
                        continue;
                    }

                    $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                    $bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];

                }
                if (isset($update_attr[$column_model->column_name]))
                {
                    if ($is_super === false && $column_model->checkUpdateAccess($this->user) === false)
                    {
                        $errors[] = "无权操作:{$column_model->column_name}";
                        continue;
                    }
                    $update[":update_{$column_model->column_name}"] = "`{$column_model->column_name}`=:update_{$column_model->column_name}";
                    $bind[":update_{$column_model->column_name}"]   = $update_attr[$column_model->column_name];
                }

            }
        }
        else
        {
            $errors[] = "无权操作:{$table_name}";
        }

        if (count($sets) === 0)
        {
            $errors[] = "无插入值";
        }
        if (count($errors))
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'] . ':[' . join(',', $errors) . ']', $errors);
        }
        else
        {

            $sets_str                = join(',', $sets);
            $on_duplicate_key_update = '';
            $op_type                 = 'insert';
            if (count($update))
            {
                $op_type                 = 'insert_update';
                $on_duplicate_key_update = ' on duplicate key update ' . join(',', $update);
            }
            $insert_sql = "insert ignore into  {$table_name} set {$sets_str} {$on_duplicate_key_update}";
            $insert_cmd = $db_table->getDbconfConnect()->setText($insert_sql);
            $res        = $insert_cmd->bindArray($bind)->execute();


            if (empty($res))
            {
                return $this->dispatcher->createInterruptionInfo(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'], [
                    'sql'  => $insert_sql,
                    'bind' => $bind,
                ]);
            }
            else
            {
                $pk_val      = $insert_cmd->lastInsertId();
                $select_sql  = "select * from {$table_name}  where `{$pk}`={$pk_val} limit 1";
                $insert_data = $db_table->getDbconfConnect()->setText($select_sql)->queryRow();

                $log_dao                 = DbOpLogDao::model();
                $log_dao->db_name        = $dbconf_name;
                $log_dao->table_name     = $table_name;
                $log_dao->row_pk         = $pk_val;
                $log_dao->op_type        = $op_type;
                $log_dao->exec_info      = json_encode([
                    'insert' => [
                        'sql'  => $insert_sql,
                        'bind' => $bind,
                        'pk'   => $pk_val,
                        'data' => $insert_data
                    ]
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $log_dao->exec_res       = $res;
                $log_dao->exec_by        = $this->user->id;
                $log_dao->log_struct_ver = '2022-11-21';
                $log_dao->insert(false);

                return [
                    'insert' => [
                        'sql'  => $insert_sql,
                        'bind' => $bind,
                        'pk'   => $pk_val,
                        'data' => $insert_data
                    ]
                ];
            }
        }
    }

}
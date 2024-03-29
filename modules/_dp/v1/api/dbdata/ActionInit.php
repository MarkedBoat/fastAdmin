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
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionInit extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        $init_sql = 'CREATE SCHEMA `fast_bg` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  ;';
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db          = 'dev_bg';
        $table_name  = $this->inputDataBox->getStringNotNull('table_name');
        $attr        = $this->inputDataBox->tryGetArray('attr');
        $update_attr = $this->inputDataBox->tryGetArray('update_attr');

        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name]))
        {
            $table_name = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name];
        }

        $is_super     = in_array('_super_admin', $this->user->role_codes, true);
        $user_roles   = $this->user->role_codes;
        $user_roles[] = '*';
        $db_table     = DbTable::model()->setTable($db, $table_name);
        $table_model  = $db_table->getBizTableInfo();
        $errors       = [];
        $bind         = [];
        $select_bind  = [];
        $sets         = [];
        $update       = [];
        $pk           = '';
        if ($is_super || array_intersect($user_roles, $table_model->access_role_codes))
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
                    if ($is_super || (array_intersect($user_roles, $column_model->access_update_role_codes) && in_array($attr[$column_model->column_name], $column_model->val_items)))
                    {
                        $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                        $bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];
                    }
                    else
                    {
                        $errors[] = "无权操作字段:{$column_model->column_name}";
                    }
                }
                if (isset($update_attr[$column_model->column_name]))
                {
                    if ($is_super || (array_intersect($user_roles, $column_model->access_update_role_codes) && in_array($attr[$column_model->column_name], $column_model->val_items)))
                    {
                        $update[":update_{$column_model->column_name}"] = "`{$column_model->column_name}`=:update_{$column_model->column_name}";
                        $bind[":update_{$column_model->column_name}"]   = $update_attr[$column_model->column_name];
                    }
                    else
                    {
                        $errors[] = "无权操作字段:{$column_model->column_name}";
                    }
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
            return $this->dispatcher->createInterruptionInfo(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'], $errors);
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
            $insert_cmd = $db_table->getDbConnect()->setText($insert_sql);
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
                $insert_data = $db_table->getDbConnect()->setText($select_sql)->queryRow();

                $log_dao                 = DbOpLogDao::model();
                $log_dao->dbconf_name    = $db;
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
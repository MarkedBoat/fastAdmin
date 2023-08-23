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


class ActionUpdate extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db_code    = $this->inputDataBox->getStringNotNull('dbconf_name');
        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        $attr       = $this->inputDataBox->tryGetArray('attr');


        $is_super      = in_array('_super_admin', $this->user->role_codes, true);
        $db_conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code, 'is_ok' => Opt::YES]);
        if ($is_super === false && $db_conf_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "需要数据库授权:[{$db_code}]", false);
        }
        $table_name       = DbTable::replaceFakeTableName($table_name);
        $table_conf_model = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $table_name, 'is_ok' => Opt::YES]);
        if ($is_super === false && $table_conf_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "需要表授权:[{$db_code}.{$table_name}]", false);
        }

        $user_roles   = $this->user->role_codes;
        $user_roles[] = '*';
        $db_table     = DbTable::model()->setBizTableModel($table_conf_model)->setTable($db_code, $table_name);
        $table_model  = $table_conf_model;
        $errors       = [];
        $bind         = [];
        $select_bind  = [];
        $sets         = [];
        $wheres       = [];
        $pk_val       = 0;
        if (1)
        {
            $column_models = $db_table->getBizTableColumns();
            Sys::app()->addLog($column_models);
            foreach ($column_models as $column_model)
            {
                if ($column_model->index_key === 'PRI')
                {
                    if (isset($attr[$column_model->column_name]))
                    {
                        $wheres[":{$column_model->column_name}"]      = "`{$column_model->column_name}`=:{$column_model->column_name}";
                        $bind[":{$column_model->column_name}"]        = $attr[$column_model->column_name];
                        $select_bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];
                        $pk_val                                       = $attr[$column_model->column_name];
                        continue;
                    }
                    else
                    {
                        $errors[] = "缺少pk:{$column_model->column_name}";
                    }

                }

                if (isset($attr[$column_model->column_name]))
                {
                    if ($is_super === false && $column_model->checkUpdateAccess($this->user) === false)
                    {
                        $errors[] = "无权操作:{$column_model->column_name}";
                        continue;
                    }
                    $val_check = true;
                    if (count($column_model->val_items))
                    {
                        $val_check = in_array($attr[$column_model->column_name], array_column($column_model->val_items, 'val'));
                    }

                    if ($val_check)
                    {
                        $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                        $bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];
                    }
                    else
                    {
                        if ($is_super)
                        {
                            $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                            $bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];
                        }
                        else
                        {
                            $errors[] = "字段值非预期: key:[{$column_model->column_name}] val:[{$attr[$column_model->column_name]}]";
                        }
                        // Sys::app()->addLog([$column_model->column_name, $column_model->column_name[$attr[$column_model->column_name],$column_model->val_items], 'xxxxxxx');
                    }
                }
            }
        }
        else
        {
            $errors[] = "无权操作:{$table_name}";
        }
        if (count($wheres) === 0)
        {
            $errors[] = "无pk值:{$table_name}";
        }
        if (count($sets) === 0)
        {
            $errors[] = "无修改值";
        }

        if (count($errors))
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'] . ':[' . join(',', $errors) . ']', $errors);
        }
        else
        {

            $sets_str   = join(',', $sets);
            $where_str  = join(' and ', $wheres);
            $update_sql = "update {$table_name} set {$sets_str} where {$where_str}";
            $select_sql = "select * from {$table_name}  where {$where_str} limit 1";
            $old_info   = $db_table->getDbconfConnect()->setText($select_sql)->bindArray($select_bind)->queryRow();
            $res        = $db_table->getDbconfConnect()->setText($update_sql)->bindArray($bind)->execute();
            $new_info   = $db_table->getDbconfConnect()->setText($select_sql)->bindArray($select_bind)->queryRow();

            $log_dao                 = DbOpLogDao::model();
            $log_dao->db_name        = $db_code;
            $log_dao->table_name     = $table_name;
            $log_dao->row_pk         = $pk_val;
            $log_dao->op_type        = 'update';
            $log_dao->exec_info      = json_encode([
                'update' => [
                    'sql'  => $update_sql,
                    'bind' => $bind,
                    'res'  => $res
                ],
                'select' => [
                    'sql'  => $select_sql,
                    'bind' => $select_bind,
                    'old'  => $old_info,
                    'new'  => $new_info
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $log_dao->exec_res       = $res;
            $log_dao->exec_by        = $this->user->id;
            $log_dao->log_struct_ver = '2022-11-21';
            $log_dao->insert(false);

            return [
                'update' => [
                    'sql'  => $update_sql,
                    'bind' => $bind,
                    'res'  => $res
                ],
                'select' => [
                    'sql'  => $select_sql,
                    'bind' => $select_bind,
                    'old'  => $old_info,
                    'new'  => $new_info
                ]
            ];
        }


    }


}
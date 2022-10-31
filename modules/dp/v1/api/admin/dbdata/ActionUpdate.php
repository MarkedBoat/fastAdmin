<?php

namespace modules\dp\v1\api\admin\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;


class ActionUpdate extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db         = 'dev_bg';
        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        $attr       = $this->inputDataBox->tryGetArray('attr');

        $is_super     = in_array('super_admin', $this->user->role_codes, true);
        $user_roles   = $this->user->role_codes;
        $user_roles[] = '*';
        $db_table     = DbTable::model()->setTable($db, $table_name);
        $table_model  = $db_table->getBizTableInfo();
        $errors       = [];
        $bind         = [];
        $select_bind  = [];
        $sets         = [];
        $wheres       = [];

        if ($is_super || array_intersect($user_roles, $table_model->read_roles))
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
                        continue;
                    }
                    else
                    {
                        $errors[] = "缺少pk:{$column_model->column_name}";
                    }

                }
                if (isset($attr[$column_model->column_name]))
                {
                    if ($is_super || array_intersect($user_roles, $column_model->add_roles) || (array_intersect($user_roles, $column_model->opt_roles) && in_array($attr[$column_model->column_name], $column_model->val_range)))
                    {
                        $sets[":{$column_model->column_name}"] = "`{$column_model->column_name}`=:{$column_model->column_name}";
                        $bind[":{$column_model->column_name}"] = $attr[$column_model->column_name];
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
            return $this->dispatcher->createInterruption(AdvError::db_save_error['detail'], AdvError::db_save_error['msg'], $errors);
        }
        else
        {

            $sets_str   = join(',', $sets);
            $where_str  = join(' and ', $wheres);
            $update_sql = "update {$table_name} set {$sets_str} where {$where_str}";
            $select_sql = "select * from {$table_name}  where {$where_str} limit 1";
            $old_info   = $db_table->getDbConnect()->setText($select_sql)->bindArray($select_bind)->queryRow();
            $res        = $db_table->getDbConnect()->setText($update_sql)->bindArray($bind)->execute();
            $new_info   = $db_table->getDbConnect()->setText($select_sql)->bindArray($select_bind)->queryRow();

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
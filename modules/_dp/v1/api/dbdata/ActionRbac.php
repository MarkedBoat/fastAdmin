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


class ActionRbac extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db_code = $this->inputDataBox->getStringNotNull('db_code');
        $at      = $this->inputDataBox->getStringNotNull('at');
        //


        if ($at === 'db')
        {
            $access_role_codes                = $this->inputDataBox->tryGetArray('accessRoleCodes');
            $db_conf_model                    = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
            $db_conf_model->access_role_codes = json_encode($access_role_codes);
            $db_conf_model->update();
            return [
                'request' => [
                    'db' => $db_code,
                ],
                'res'     => [
                    'attr' => $db_conf_model->getOpenInfo(),
                ]
            ];
        }

        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        if ($at === 'table')
        {
            $access_role_codes        = $this->inputDataBox->tryGetArray('accessRoleCodes');
            $access_insert_role_codes = $this->inputDataBox->tryGetArray('accessInsertRoleCodes');

            $db_conf_model                           = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $table_name]);
            $db_conf_model->access_role_codes        = json_encode($access_role_codes);
            $db_conf_model->access_insert_role_codes = json_encode($access_insert_role_codes);

            $db_conf_model->update();
            return [
                'request' => [
                    'db' => $db_code,
                ],
                'res'     => [
                    'attr' => $db_conf_model->getOpenInfo(),
                ]
            ];
        }
        $role_code = $this->inputDataBox->getStringNotNull('role_code');
        $rbacInfos = $this->inputDataBox->getArrayNotNull('columnRbacInfos');

        if ($at === 'columns')
        {
            $column_sqls             = [];
            $data_column_tn          = DbColumn::$tableName;
            $cnn                     = DbColumn::model()->getDbConnect();
            $col_map                 = [
                'accessSelectRoles' => 'access_select_role_codes',
                'accessUpdateRoles' => 'access_update_role_codes',

            ];
            $select_role_column_name = 'access_select_role_codes';
            $update_role_column_name = 'access_update_role_codes';

            foreach ($rbacInfos as $rbacInfo)
            {
                $data_col_name = $rbacInfo['column_name'];
                //  $data_col_name= $col_map[$data_col_name];
                $column_sqls[$data_col_name] = [
                    'info'   => $rbacInfo,
                    'select' => ['type' => '', 'sql' => '', 'res' => 0],
                    'update' => ['type' => '', 'sql' => '', 'res' => 0]
                ];
                if ($rbacInfo['select'] === "true")
                {
                    $sql = "update  {$data_column_tn} set `{$select_role_column_name}`=json_array_append(`{$select_role_column_name}`,'$','{$role_code}')  where `dbconf_name`='{$db_code}' and `table_name`='{$table_name}' and  `column_name`='{$data_col_name}' and  JSON_SEARCH(`{$select_role_column_name}`,'one','{$role_code}') IS NULL;";

                    $column_sqls[$data_col_name]['select'] = ['type' => 'add', 'sql' => $sql, 'res' => intval($cnn->setText($sql)->execute())];
                }
                else
                {
                    $update_sql = "update  {$data_column_tn} set `{$select_role_column_name}`=JSON_REMOVE(`{$select_role_column_name}`, JSON_UNQUOTE(JSON_SEARCH(`{$select_role_column_name}`, 'one', '{$role_code}')))  where  `dbconf_name`='{$db_code}' and `table_name`='{$table_name}' and  `column_name`='{$data_col_name}' and   JSON_SEARCH(`{$select_role_column_name}`,'one','{$role_code}') IS NOT NULL;";

                    $column_sqls[$data_col_name]['select'] = ['type' => 'remove', 'sql' => $update_sql, 'res' => intval($cnn->setText($update_sql)->execute())];
                }
                if ($rbacInfo['update'] === "true")
                {
                    $sql = "update  {$data_column_tn} set `{$update_role_column_name}`=json_array_append(`{$update_role_column_name}`,'$','{$role_code}')  where `dbconf_name`='{$db_code}' and `table_name`='{$table_name}' and  `column_name`='{$data_col_name}' and  JSON_SEARCH(`{$update_role_column_name}`,'one','{$role_code}') IS NULL;";

                    $column_sqls[$data_col_name]['update'] = ['type' => 'add', 'sql' => $sql, 'res' => intval($cnn->setText($sql)->execute())];
                }
                else
                {
                    $update_sql = "update  {$data_column_tn} set `{$update_role_column_name}`=JSON_REMOVE(`{$update_role_column_name}`, JSON_UNQUOTE(JSON_SEARCH(`{$update_role_column_name}`, 'one', '{$role_code}')))  where  `dbconf_name`='{$db_code}' and `table_name`='{$table_name}' and  `column_name`='{$data_col_name}' and   JSON_SEARCH(`{$update_role_column_name}`,'one','{$role_code}') IS NOT NULL;";

                    $column_sqls[$data_col_name]['update'] = ['type' => 'remove', 'sql' => $update_sql, 'res' => intval($cnn->setText($update_sql)->execute())];

                }
            }

            return $column_sqls;

            die;
        }


        $wheres     = [];
        $table_name = $this->inputDataBox->tryGetString('table_name');
        if ($table_name)
        {
            $wheres[] = "`dbconf_name`='{$db_code}'";
            $wheres[] = "`table_name`='{$table_name}'";

            $colmun_name = $this->inputDataBox->tryGetString('column_name');
            if ($colmun_name)
            {
                $wheres[]      = "`column_name`='{$colmun_name}'";
                $data_col_name = $operation;
                $t_name        = DbColumn::$tableName;
            }
            else
            {
                $data_col_name = $operation;
                $t_name        = DbTable::$tableName;
            }
        }
        else
        {
            $data_col_name = $operation;
            $t_name        = DbDbConf::$tableName;
            $wheres[]      = "`db_code`='{$db_code}'";

        }
        $where_str  = join(' and ', $wheres);
        $update_sql = '';
        $select_sql = "select `{$data_col_name}` FROM {$t_name}   where {$where_str};";
        if ($type === 'add')
        {
            //$sql_select = "select id,`{$data_col_name}` FROM {$t_name} where {$where_str} and  JSON_SEARCH(`{$data_col_name}`,'one','{$role_code}') IS NOT NULL;";
            $update_sql = "update FROM {$t_name} set `{$data_col_name}`=json_array_append(`{$data_col_name}`,'$','{$role_code}')  where {$where_str} and  JSON_SEARCH(`{$data_col_name}`,'one','{$role_code}') IS NULL;";

        }
        else if ($type === 'remove')
        {
            $update_sql = "update FROM {$t_name} set `{$data_col_name}`=JSON_REMOVE(`{$data_col_name}`, JSON_UNQUOTE(JSON_SEARCH(`{$data_col_name}`, 'one', '{$role_code}')))  where  {$where_str} and   JSON_SEARCH(`{$data_col_name}`,'one','{$role_code}') IS NOT NULL;";
        }
        else
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_error['detail'], "不删除，也不增加，要干啥? [{$type}]", false);
        }

        return [
            'update' => [
                'sql' => $update_sql,
                'res' => intval($cnn->setText($update_sql)->execute())
            ],
            'select' => [
                'sql' => $select_sql,
                'new' => $cnn->setText($select_sql)->queryScalar()
            ]
        ];

    }


}
<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionSelect extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db_code    = $this->inputDataBox->getStringNotNull('dbconf_name');
        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        $attr       = $this->inputDataBox->tryGetArray('attr');
        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');

        $is_super = in_array('super_admin', $this->user->role_codes, true);
        $db_conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code, 'is_ok' => Opt::YES]);
        if ($is_super === false && $db_conf_model->checkAllAccess($this->user) === false && $db_conf_model->checkReadAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruption(AdvError::rbac_deny['detail'], "无权访问Db:[{$db_code}]", false);
        }
        $table_name       = DbTable::replaceFakeTableName($table_name);
        $table_conf_model = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $table_name, 'is_ok' => Opt::YES]);
        if ($is_super === false && $table_conf_model->checkAllAccess($this->user) === false && $table_conf_model->checkReadAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruption(AdvError::rbac_deny['detail'], "无权访问表:[{$db_code}.{$table_name}]", false);
        }
        $dbconf_name = $db_code;


        $dbtable = DbTable::model()->setBizTableModel($table_conf_model)->setTable($dbconf_name, $table_name);
        $info    = $dbtable->getInfo();


        $dbtable->setAttrs($attr)->setPage($page_index, $page_size);
        foreach ($sort_map as $sort_key => $sort_type)
        {
            $dbtable->addSort($sort_key, $sort_type);
        }
        $res = $dbtable->query();
        if ($is_super === false)
        {
            $deny_keys = [];
            foreach ($info['columns'] as $i => $col_info)
            {
                if (count($col_info['read_roles']) && count(array_intersect($this->user->role_codes, $col_info['read_roles'])) === 0)
                {
                    $deny_keys[] = $col_info['column_name'];
                }
            }
            if (count($deny_keys))
            {
                foreach ($res['dataRows'] as $rowIndex => $dataRow)
                {
                    foreach ($deny_keys as $deny_key)
                    {
                        unset($res['dataRows'][$rowIndex][$deny_key]);
                    }
                }
            }
        }

        return $res;

    }


}
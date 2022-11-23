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
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionSelect extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db         = 'dev_bg';
        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        $attr       = $this->inputDataBox->tryGetArray('attr');
        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');

        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name]))
        {
            $table_name = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name];
        }

        $is_super = in_array('super_admin', $this->user->role_codes, true);


        $dbtable = DbTable::model()->setTable($db, $table_name);
        $info    = $dbtable->getInfo();

        if (!($is_super || count($info['table']['read_roles']) === 0 || count(array_intersect($this->user->role_codes, $info['table']['read_roles'])) === 0))
        {
            return $this->dispatcher->createInterruption(AdvError::rbac_deny['detail'], "无权访问:[{$table_name}]", false);
        }


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
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


class ActionInfo extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;

        $db         = 'dev_bg';
        $table_name = $this->inputDataBox->getStringNotNull('table_name');

        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name]))
        {
            $table_name = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name];
        }

        $info                         = DbTable::model()->setTable($db, $table_name)->getInfo();
        $is_super                     = in_array('super_admin', $this->user->role_codes, true);
        $info['table']['is_readable'] = $is_super || count($info['table']['read_roles']) === 0 || array_intersect($this->user->role_codes, $info['table']['read_roles']);
        $info['table']['is_addable']  = $is_super || count($info['table']['add_roles']) === 0 || array_intersect($this->user->role_codes, $info['table']['add_roles']);

        $colinfos = [];
        if ($info['table']['is_readable'])
        {
            foreach ($info['columns'] as $i => $col_info)
            {
                $col_info['is_readable'] = $is_super || count($col_info['read_roles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['read_roles'])) > 0;
                $col_info['is_readable2']=[
                    array_intersect($this->user->role_codes, $col_info['read_roles'])
                ];
                $col_info['is_optable']  = $is_super || count($col_info['opt_roles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['opt_roles'])) > 0;
                $col_info['is_addable']  = $is_super || count($col_info['add_roles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['add_roles'])) > 0;
                if ($col_info['is_readable'])
                {
                    $colinfos[] = $col_info;
                }

            }
        }

        $info['columns'] = $colinfos;
        return $info;

    }


}
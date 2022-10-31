<?php

namespace modules\dp\v1\api\admin\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;


class ActionInfo extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;

        $db         = 'dev_bg';
        $table_name = $this->inputDataBox->getStringNotNull('table_name');


        $info                            = DbTable::model()->setTable($db, $table_name)->getInfo();
        $is_super                        = in_array('super_admin', $this->user->role_codes, true);
        $info['table']['is_readable']    = $is_super || (in_array('*', $info['table']['read_roles']) || array_intersect($this->user->role_codes, $info['table']['read_roles']));
        $info['table']['is_row_addable'] = $is_super || (in_array('*', $info['table']['add_roles']) || array_intersect($this->user->role_codes, $info['table']['add_roles']));

        $colinfos = [];
        if ($info['table']['is_readable'])
        {
            foreach ($info['columns'] as $i => $col_info)
            {
                $col_info['is_readable']    = $is_super || (in_array('*', $col_info['read_roles']) || array_intersect($this->user->role_codes, $col_info['read_roles']));
                $col_info['is_val_optable'] = $is_super || (in_array('*', $col_info['opt_roles']) || array_intersect($this->user->role_codes, $col_info['opt_roles']));
                $col_info['is_val_addable'] = $is_super || (in_array('*', $col_info['add_roles']) || array_intersect($this->user->role_codes, $col_info['add_roles']));
                $colinfos[]                 = $col_info;

            }
        }

        $info['columns'] = $colinfos;
        return $info;

    }


}
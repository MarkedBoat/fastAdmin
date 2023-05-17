<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionInfo extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;

        //  $dbconf_name = $this->inputDataBox->getStringNotNull('dbconf_name');
        $table_name = $this->inputDataBox->getStringNotNull('table_name');


        $table_name = DbTable::replaceFakeTableName($table_name);



        $db_code = $this->inputDataBox->getStringNotNull('dbconf_name');

        $conf_model  = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
        $dbconf_name = $db_code;


        $info                         = DbTable::model()->setTable($dbconf_name, $table_name)->getInfo();
        $is_super                     = in_array('_super_admin', $this->user->role_codes, true);
        $info['table']['is_readable'] = $is_super || count($info['table']['accessRoles']) === 0 || array_intersect($this->user->role_codes, $info['table']['accessRoles']);
        $info['table']['is_addable']  = $is_super || count($info['table']['accessInsertRoles']) === 0 || array_intersect($this->user->role_codes, $info['table']['accessInsertRoles']);

        $colinfos = [];
        if ($info['table']['is_readable'])
        {
            foreach ($info['columns'] as $i => $col_info)
            {
                $col_info['is_readable']  = $is_super || count($col_info['accessSelectRoles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['accessSelectRoles'])) > 0;
                $col_info['is_readable2'] = [
                    array_intersect($this->user->role_codes, $col_info['accessSelectRoles'])
                ];
                $col_info['is_optable']   = $is_super || count($col_info['accessUpdateRoles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['accessUpdateRoles'])) > 0;
                $col_info['is_addable']   = $is_super || count($col_info['accessUpdateRoles']) === 0 || count(array_intersect($this->user->role_codes, $col_info['accessUpdateRoles'])) > 0;
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
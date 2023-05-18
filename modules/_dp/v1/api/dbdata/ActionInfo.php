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


        $db_code    = $this->inputDataBox->getStringNotNull('dbconf_name');
        $is_super   = in_array('_super_admin', $this->user->role_codes, true);
        $conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
        if ($is_super === false && $conf_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruption(AdvError::rbac_deny['detail'], "无权访问Db:[{$db_code}]", false);
        }
        $dbconf_name = $db_code;

        $table_model = DbTable::model()->setTable($dbconf_name, $table_name);
        if ($is_super === false && $table_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruption(AdvError::rbac_deny['detail'], "无权访问表:[{$db_code}.{$table_name}]", false);
        }

        $is_table_add_able = true;
        if ($is_super === false && $table_model->checkInsertAccess($this->user) === false)
        {
            $is_table_add_able = false;
        }


        $biz_table_model     = $table_model->getBizTableInfo();
        $biz_column_models   = $table_model->getBizTableColumns();
        $biz_relation_models = $table_model->getBizTableRelations();


        $colinfos = [];

        if ($is_super === false)
        {
            foreach ($biz_column_models as $column_model)
            {
                if ($column_model->checkSelectAccess($this->user))
                {
                    $col_info           = $column_model->getOpenInfo();
                    $col_info['__read'] = true;
                    $colinfos[]         = $col_info;
                }
            }
        }
        else
        {
            foreach ($biz_column_models as $column_model)
            {
                $colinfos[] = $column_model->getOpenInfo();
            }
        }

        $relat_infos = [];
        foreach ($biz_relation_models as $biz_relation_model)
        {
            $relat_infos[] = $biz_relation_model->getAllInfo();
        }

        return [
            'table'     => $biz_table_model->getOpenInfo(),
            'columns'   => $colinfos,
            'relations' => $relat_infos,
            'user'      => $this->user->getAllInfo()
        ];
    }


}
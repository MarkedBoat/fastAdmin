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


class ActionQuery extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db_code    = $this->inputDataBox->getStringNotNull('dbcode');
        $sql        = $this->inputDataBox->getStringNotNull('sql');
        $bind       = $this->inputDataBox->tryGetArray('bind');
        $conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code]);
        $db_cnn     = $conf_model->getConfDbConnect();
        $sql2       = $sql;
        foreach ($bind as $k => $v)
        {
            $sql2 = str_replace($k, is_numeric($v) ? $v : "'{$v}'", $sql2);
        }

        $res = [
            'newSql' => $sql2,
            'effect' => 0,
            'data'   => [
                'dataRows'  => [],
                'pageIndex' => 1,
                'pageTotal' => 1,
                'pageSize'  => 1000000,
                'rowsTotal' => 0,
            ],
        ];
        if (strtolower(substr($sql, 0, 6)) === 'select')
        {
            $res['data']['dataRows']  = $db_cnn->setText($sql)->bindArray($bind)->queryAll();
            $res['data']['rowsTotal'] = count($res['data']['dataRows']);
        }
        else
        {
            $res['effect'] = $db_cnn->setText($sql)->bindArray($bind)->execute();
        }
        return $res;
    }

}
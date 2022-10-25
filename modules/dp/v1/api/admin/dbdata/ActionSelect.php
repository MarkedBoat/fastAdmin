<?php

namespace modules\bee_invasion\v1\api\admin\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\dbdata\DbColumn;
use modules\bee_invasion\v1\model\admin\dbdata\DbTable;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;


class ActionSelect extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;
        $db         = 'bee_invade';
        $table_name = $this->inputDataBox->getStringNotNull('table_name');
        $attr       = $this->inputDataBox->tryGetArray('attr');
        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');

        $dbtable = new DbTable();
        $dbtable->setTable($db, $table_name)->setAttrs($attr)->setPage($page_index, $page_size);
        foreach ($sort_map as $sort_key => $sort_type)
        {
            $dbtable->addSort($sort_key, $sort_type);
        }
        return $dbtable->query();

    }


}
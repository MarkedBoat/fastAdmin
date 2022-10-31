<?php

namespace modules\dp\v1\api\admin\js;

use Cassandra\Column;
use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbStruct;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\economy\ConsumableGoods;
use modules\dp\v1\model\economy\Currency;
use modules\dp\v1\model\economy\MObject;
use modules\dp\v1\model\play\Equipment;


class ActionDbStructs extends ActionBase
{
    public $requestMethods = ['GET'];

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        $daos  = DbStruct::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]);
        $datas = [];
        foreach ($daos as $dao)
        {
            $datas[] = $dao->getOpenInfo();
        }
        $json = json_encode($datas);
        echo "\nwindow.db_struct={$json};\n";
        \models\Api::$hasOutput = true;
    }

}
<?php

namespace modules\bee_invasion\v1\api\admin\js;

use Cassandra\Column;
use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\rbac\RbacRoleDao;
use modules\bee_invasion\v1\model\admin\dbdata\DbColumn;
use modules\bee_invasion\v1\model\admin\dbdata\DbTable;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\play\Equipment;


class ActionItems extends ActionBase
{
    public $requestMethods = ['GET'];

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        $daoss = [
            'currency'  => Currency::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'cg'        => ConsumableGoods::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'object'    => MObject::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'equipment' => Equipment::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
        ];
        $datas = [];
        foreach ($daoss as $class => $daos)
        {
            /** @var  $dao  Currency|ConsumableGoods|MObject | Equipment */
            foreach ($daos as $dao)
            {
                $info               = $dao->getOpenInfo();
                $info['item_flag']  = $class . '/' . $dao->item_code;
                $info['class_code'] = $class;
                $datas[]            = $info;
            }
        }

        $json = json_encode($datas);
        echo "\nwindow.bi_items={$json};\n";
        \models\Api::$hasOutput = true;
    }

}
<?php

namespace modules\bee_invasion\v1\model\role;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RoleEquipmentHisDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyHisDao;
use modules\bee_invasion\v1\model\role\RoleEquipment;
use modules\bee_invasion\v1\model\user\TUserOperationHistory;
use modules\bee_invasion\v1\model\user\UserCg;
use modules\bee_invasion\v1\model\user\UserCurrency;


class RoleEquipmentHis extends RoleEquipmentHisDao
{
    use TUserOperationHistory;
    const src_map = [
        //'order_pay'   => ['val' => 'order_pay', 'op_type' => 2,],
        'order_goods' => ['val' => 'order_goods', 'op_type' => 1,],

    ];
    //const srcUsed       = 'used';
    //const srcOrderPay   = 'order_pay';
    const srcOrderGoods = 'order_goods';


    public function initUserAccountModel()
    {
        $this->userAccountModel = new RoleEquipment();
    }


}
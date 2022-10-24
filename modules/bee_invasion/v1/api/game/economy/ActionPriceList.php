<?php

namespace modules\bee_invasion\v1\api\game\economy;

use models\common\ActionBase;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;
use modules\bee_invasion\v1\dao\game\economy\PriceListDao;


class ActionPriceList extends GameBaseAction
{
    public function run()
    {
        $goods_codes_str = $this->inputDataBox->tryGetString('goods');
        $payment         = $this->inputDataBox->tryGetString('currency');
        $item_type       = $this->inputDataBox->tryGetString('pkg_type');
        $goods_item_code = $this->inputDataBox->tryGetString('goods_item_code');

        $condtions = [
            'is_ok' => Opt::isOk
        ];
        if ($payment)
        {
            $condtions['pay_item_code'] = $payment;
        }
        if ($goods_item_code)
        {
            $condtions['goods_item_code'] = $goods_item_code;
        }
        if ($goods_codes_str)
        {
            $goods_codes = explode('/', $goods_codes_str);
            foreach ($goods_codes as $i => $goods_code)
            {
                $condtions["goods_item_class->'$[{$i}]'"] = $goods_code;
            }
        }

        if (in_array($item_type, ['pkg', 'custom']))
        {
            $condtions['item_type'] = $item_type;
        }

        $list   = [];
        $models = PriceListDao::model()->findAllByWhere($condtions);
        foreach ($models as $model)
        {
            $list[] = $model->getOpenInfo();
        }
        $order_sns = array_column($list, 'order_num');
        array_multisort($order_sns, SORT_ASC, SORT_NUMERIC, $list);
        return ['list' => $list];

    }
}
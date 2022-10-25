<?php

namespace modules\bee_invasion\v1\api\free;

use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\user\UserFakeDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionComputeNumber extends ActionBase
{

    public function run()
    {
        $number  = $this->inputDataBox->getStringNotNull('number');
        $pow_num = $this->inputDataBox->getIntNotNull('pow_number');


        $numbers         = explode('.', $number);
        $int_number      = intval($numbers[0]);
        $tail_number     = 0;
        $tail_pad_number = 0;

        $tail_zoom_times  = 0;
        $tail_number_base = 0;
        if (isset($numbers[1]))
        {
            $tail_number_base = $numbers[1];
            $len              = strlen($tail_number_base);
            if ($len < $pow_num)
            {
                $tail_pad_number = str_pad($tail_number_base, $pow_num, 0);
            }
            else
            {
                $tail_pad_number = $tail_number_base;
            }
            $tail_number = intval($tail_pad_number);

        }


        $computed_int_number = bcmul($int_number, pow(10, $pow_num));

        return [
            'int'     => [
                'base'     => $int_number,
                'computed' => $computed_int_number
            ],
            'decimal' => [
                'base'     => $tail_number_base,
                'pad'      => $tail_pad_number,
                'computed' => $tail_number
            ],
            'value'   => bcadd($computed_int_number, $tail_number),
        ];


    }
}
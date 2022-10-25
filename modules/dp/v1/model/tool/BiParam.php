<?php

namespace modules\bee_invasion\v1\model\tool;


use models\common\param\Param;
use models\common\sys\Sys;

class BiParam extends Param
{
    const dbNumberRate = 100000000;

    public static function dbNumber2DisplayNumber($number)
    {
        return $number / self::dbNumberRate;
    }

    public static function displayNumber2DbNumber($number)
    {
        $pow_num     = 8;
        $numbers     = explode('.', $number);
        $int_number  = intval($numbers[0]);
        $tail_number = 0;

        if (isset($numbers[1]))
        {
            $tail_number_base = $numbers[1];
            $len              = strlen($tail_number_base);
            $tail_pad_number  = $len < $pow_num ? str_pad($tail_number_base, $pow_num, 0) : $tail_number_base;
            $tail_number      = intval($tail_pad_number);
        }
        $computed_int_number = bcmul($int_number, self::dbNumberRate);
        return bcadd($computed_int_number, $tail_number);
    }


    public static function getDbBlockOpenIndex($true_index = 0)
    {
        $open_code = self::std10Number10ToNew30($true_index);
        return str_pad($open_code, 2, 'v', STR_PAD_LEFT);//本意是补0，不过现在0被v 代替了
    }

    public static function getCurrentDbBlockOpenIndex()
    {
        return self::getDbBlockOpenIndex(Sys::app()->params['database_block_index']);
    }

    /**
     * 标准10进制 改为 非标准 31进制
     * @param $number10
     * @return string|string[]
     */
    public static function std10Number10ToNew30($number10)
    {
        return str_replace(['4', '0', '1', 'i', 'l', 'o'], ['u', 'v', 'w', 'x', 'y', 'z'], base_convert($number10, 10, 30));
    }

    /**
     * 非标准 31进制 改为  标准10进制
     * @param $number31
     * @return int
     */
    public static function new30numberToStd10($number31)
    {
        return intval(base_convert(str_replace(['u', 'v', 'w', 'x', 'y', 'z'], ['4', '0', '1', 'i', 'l', 'o'], $number31), 30, 10));
    }
}

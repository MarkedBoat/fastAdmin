<?php


namespace models\common\opt;


class Opt
{
    const isOk    = 1;
    const isNotOk = 2;

    const YES = 1;
    const NOT = 2;


    const valueType_amount = 'amount';     //数量，只能是 增加 减少，不可直接设置，比如通货、消耗性道具等
    const valueType_value  = 'value';      //值，只能设置 覆盖，比如 用户装备
    const valueType_note   = 'note';       //凭据，只能设置 {note:xxxx,expires:1111} 比如广告、门票
    // const valueType_timesLimit = 'timesLimit'; //次数限制，只能设置 {times:111,}

    const noteStatus_useless = 7;//作为不能再使用的 临界点
    const noteStatus_del     = 9;

    const operationIncome = 1;//收入
    const operationPay    = 2;//支出

}
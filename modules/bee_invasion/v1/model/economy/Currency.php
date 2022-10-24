<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\economy\CurrencyDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Currency extends CurrencyDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'CurrencyItemCodes';
    const cacheConfigKey_Info      = 'CurrencyInfo';


    private static $item_codes = [];
    private static $item_infos = [];


    public function getOpenInfo()
    {
        return [
            'id'             => intval($this->id),
            'item_name'      => $this->item_name,
            'item_code'      => $this->item_code,
            'item_icon'      => $this->item_icon,
            'item_detail'    => $this->item_detail,
            'has_ui'         => intval($this->has_ui),
            'cash_price'     => intval($this->cash_price),
            'decimal_places' => $this->decimal_places,
            'opts'           => $this->getJsondecodedValue($this->opts, 'object'),
        ];
    }


}
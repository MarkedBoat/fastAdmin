<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\economy\CurrencyDao;
use modules\bee_invasion\v1\dao\game\economy\ObjectDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class MObject extends ObjectDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'ObjectItemCodes';
    const cacheConfigKey_Info      = 'ObjectInfo';


    private static $item_codes = [];
    private static $item_infos = [];


}
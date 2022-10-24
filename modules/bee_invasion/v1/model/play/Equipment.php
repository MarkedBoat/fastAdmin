<?php

namespace modules\bee_invasion\v1\model\play;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\EquipmentDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Equipment extends EquipmentDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'EquipmentItemCodes';
    const cacheConfigKey_Info      = 'EquipmentInfo';

    private static $item_codes = [];
    private static $item_infos = [];

}
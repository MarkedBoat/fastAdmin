<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\economy\PriceListDao;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\dao\game\notice\NoticeDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\TItem;

class PriceItem extends PriceListDao
{
    use TInfo;


    const cacheConfigKey_LastedPks = 'PriceItemLastedPks';
    const cacheConfigKey_Info      = 'PriceItemInfo';


    private static $last_pks = [];
    private static $models   = [];


}
<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Note extends NoteDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'NoteItemCodes';
    const cacheConfigKey_Info      = 'NoteInfo';




    private static $item_codes = [];
    private static $item_infos = [];




}
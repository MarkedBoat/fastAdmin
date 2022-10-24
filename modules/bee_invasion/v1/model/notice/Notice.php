<?php

namespace modules\bee_invasion\v1\model\notice;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\dao\game\notice\NoticeDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\TItem;

class Notice extends NoticeDao
{
    use TInfo;


    const cacheConfigKey_LastedPks = 'NoticeLastedPks';
    const cacheConfigKey_Info      = 'NoticeInfo';


    private static $last_pks = [];
    private static $models   = [];


}
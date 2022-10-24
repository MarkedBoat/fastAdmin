<?php

namespace modules\bee_invasion\v1\model\economy\plat;


use models\common\db\ORM;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\economy\PriceListDao;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\dao\game\notice\NoticeDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\TItem;

class Partner extends PlatSrcDao
{
    use TInfo;


    const cacheConfigKey_LastedPks = 'PartnerLastedPks';
    const cacheConfigKey_Info      = 'PartnerInfo';


    private static $last_pks = [];
    private static $models   = [];

    public function getMainIndexField()
    {
        return 'src_code';
    }

    public function getStringSign($str)
    {
        $pri = $this->pri_key;
        return RSA::sign($str, $pri);
    }

}
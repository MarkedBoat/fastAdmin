<?php

namespace modules\bee_invasion\v1\model\play;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Perk extends PerkDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'PerkItemCodes';
    const cacheConfigKey_Info      = 'PerkInfo';

    private static $item_codes = [];
    private static $item_infos = [];



    private $dao;


    /**
     * @return ORM|PerkDao
     */
    public function getDao()
    {
        if (empty($this->dao))
        {
            $this->dao = PerkDao::model();
        }
        return $this->dao;

    }


}
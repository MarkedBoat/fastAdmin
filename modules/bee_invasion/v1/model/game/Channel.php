<?php

namespace modules\bee_invasion\v1\model\game;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\ChannelDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Channel extends ChannelDao
{
    use TItem;

    const cacheConfigKey_ItemCodes = 'ChannelItemCodes';
    const cacheConfigKey_Info      = 'ChannelInfo';

    private static $item_codes = [];
    private static $item_infos = [];



    private $dao;


    /**
     * @return ORM|ChannelDao
     */
    public function getDao()
    {
        if (empty($this->dao))
        {
            $this->dao = ChannelDao::model();
        }
        return $this->dao;

    }
}
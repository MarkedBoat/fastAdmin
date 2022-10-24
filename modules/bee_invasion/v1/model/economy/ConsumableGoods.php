<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class ConsumableGoods extends CgDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'CgItemCodes';
    const cacheConfigKey_Info      = 'CgInfo';


    private static $item_codes = [];
    private static $item_infos = [];


    private $dao;


    /**
     * @return ORM|CgDao
     */
    public function getDao()
    {
        if (empty($this->dao))
        {
            $this->dao = CgDao::model();
        }
        return $this->dao;

    }

    public function getOpenInfo()
    {
        return [
            'id'             => intval($this->id),
            'item_name'      => $this->item_name,
            'item_code'      => $this->item_code,
            'item_icon'      => $this->item_icon,
            'item_detail'    => $this->item_detail,
            'has_ui'         => intval($this->has_ui),
            'threshold'      => $this->getJsondecodedValue($this->threshold, 'object'),
            'effect'         => $this->getJsondecodedValue($this->effect, 'object'),
            'decimal_places' => $this->decimal_places,
            'opts'           => $this->getJsondecodedValue($this->opts, 'object'),
        ];
    }

}
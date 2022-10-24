<?php

namespace modules\bee_invasion\v1\model\game;


use models\common\db\ORM;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\ConfigDao;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

class Config extends ConfigDao
{
    use TItem;


    const cacheConfigKey_ItemCodes = 'ConfigItemCodes';
    const cacheConfigKey_Info      = 'ConfigInfo';

    private static $item_codes = [];
    private static $item_infos = [];


    private $dao;


    /**
     * @return ORM|ConfigDao
     */
    public function getDao()
    {
        if (empty($this->dao))
        {
            $this->dao = ConfigDao::model();
        }
        return $this->dao;
    }

    public function getLimitInfo($config_item_code)
    {

        //   if($config_info[])

        if ($config_item_code === 'user_ad_times')
        {
            $config     = $this->getItemByCode($config_item_code);
            $range_unit = $config->setting['range_unit'];
            $cycle_unit = $config->setting['cycle_unit'];

            $now_range_num = date($range_unit, time());
            $now_cycle_num = date($cycle_unit, time());

            $datas = $config->setting['opts'];
            $res   = false;
            foreach ($datas as $data)
            {
                $start = $data['start'];
                $end   = $data['end'];
                if ($start < $end)
                {
                    if ($start <= $now_range_num && $now_range_num < $end)
                    {
                        $res = ["{$now_cycle_num}_{$start}_{$end}", $data['times']];
                        break;
                    }
                }
                else if ($start > $end)
                {
                    if ($start <= $now_range_num || $now_range_num < $end)
                    {
                        $res = ["{$now_cycle_num}_{$start}_{$end}", $data['times']];
                        break;
                    }
                }
            }
            return $res ? $res : false;

        }
    }


}
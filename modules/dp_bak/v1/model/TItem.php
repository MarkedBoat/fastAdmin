<?php

namespace modules\dp\v1\model;


use models\common\db\ORM;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\model\user\CUserAccount;


Trait TItem
{

    use  TCache;


    public static $field_map = [
        'item_name'   => ['db_type' => 'varchar', 'length' => 64, 'attr' => 'itemName'],
        'item_code'   => ['db_type' => 'varchar', 'length' => 64, 'attr' => 'itemCode'],
        'item_icon'   => ['db_type' => 'varchar', 'length' => 255, 'attr' => 'itemIcon'],
        'item_detail' => ['db_type' => 'varchar', 'length' => 255, 'attr' => 'itemDetail'],
        'threshold'   => ['db_type' => 'json', 'length' => 0, 'attr' => 'itemThreshold'],
        'effect'      => ['db_type' => 'json', 'length' => 0, 'attr' => 'itemEffect'],
    ];


    /**
     * 获取所有通货code
     * @param bool $force_flush
     * @return array|mixed
     * @throws \Exception
     */
    public function getItemCodes($force_flush = false)
    {
        $res = '';
        if ($force_flush === false)
        {
            if (count(self::$item_codes) > 0)
            {
                return self::$item_codes;
            }
            $res = $this->getCache(self::cacheConfigKey_ItemCodes, false, false);
        }
        if (empty($res) || $force_flush)
        {
            $models           = $this->findAllByWhere(['is_ok' => Opt::isOk]);
            self::$item_codes = [];

            foreach ($models as $model)
            {
                self::$item_codes[]                  = $model->item_code;
                self::$item_infos[$model->item_code] = $model;
                $this->setCache(self::cacheConfigKey_Info, ['item_code' => $model->item_code], $model->getOuterDataArray());

            }
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg'][self::cacheConfigKey_ItemCodes]['key'], json_encode(self::$item_codes));
        }
        else
        {
            self::$item_codes = json_decode($res, true);
        }
        return self::$item_codes;


    }

    /**
     * @param $item_code
     * @param bool $force_flush
     * @return ORM|static|false
     * @throws AdvError
     */
    public function getItemByCode($item_code, $force_flush = false)
    {
        if (isset(self::$item_infos[$item_code]) && $force_flush === false)
        {
            return self::$item_infos[$item_code];
        }
        $res = $force_flush ? [] : $this->getCache(self::cacheConfigKey_Info, ['item_code' => $item_code]);

        if ($force_flush || empty($res))
        {// 有没有key 和   val 是空值 是两码事
            $dao = $this->findOneByWhere(['item_code' => $item_code]);
            if (empty($dao))
            {
                throw new AdvError(AdvError::res_not_exist, "不存在:[{$item_code}]");
            }
            self::$item_infos[$item_code] = $dao;
            $this->setCache(self::cacheConfigKey_Info, ['item_code' => $item_code], $dao->getOuterDataArray());
        }
        else
        {
            self::$item_infos[$item_code] = (new static())->loadData($res);
        }
        return self::$item_infos[$item_code];
    }

    /**
     * @param bool $force_flush
     * @return static[]
     * @throws AdvError
     */
    public function getItemInfos($force_flush = false)
    {
        if ($force_flush === false && count(self::$item_infos) !== 0)
        {
            return self::$item_infos;
        }

        if ($force_flush)
        {
            $this->getItemCodes(true);
        }
        else
        {
            $items_codes = $this->getItemCodes();
            if (count(self::$item_infos) === 0)
            {
                //说明 getItemCodes 走的缓存，所以更新
                $params = [];
                foreach ($items_codes as $items_code)
                {
                    $params[] = $this->getCacheKey(self::cacheConfigKey_Info, ['item_code' => $items_code]);
                }
                $jsons = Sys::app()->redis('cache')->mget($params);

                if (count($jsons) === count($items_codes) && !in_array(false, $jsons, true))
                {
                    foreach ($jsons as $json)
                    {
                        $data = json_decode($json, true);
                        if (empty($data) || !isset($data['item_code']))
                        {
                            return $this->getItemInfos(true);
                        }
                        self::$item_infos[$data['item_code']] = (new static())->loadData($data);
                    }
                }
                else
                {
                    return $this->getItemInfos(true);
                }
            }
        }
        return self::$item_infos;
    }

    /**
     * @param int $amount 要输入的值
     * @param int $amount_decimal_places 精度，指在小数点后多少位，与amount组合用以描述 小数，但是以整形来保留精度
     * @return float|int
     * @throws AdvError
     */
    public function getComputeAmount($amount, $amount_decimal_places = 0)
    {
        if ($this->decimal_places < $amount_decimal_places)
        {
            throw  new AdvError(AdvError::code_error, '输入精度高于设定值');
        }

        return $amount * pow(10, $this->decimal_places - $amount_decimal_places);
    }

    /**
     * 获取展示的数值，非存储的值
     * @param $item_amount
     * @return float|int
     */
    public function getDisplayNumber($item_amount)
    {
        return $item_amount / pow(10, $this->decimal_places);
    }

}
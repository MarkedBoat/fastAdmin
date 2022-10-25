<?php

namespace modules\bee_invasion\v1\model;


use models\common\db\ORM;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\user\CUserAccount;


Trait TInfo
{

    use  TCache;

    /**
     * @return string
     * @throws
     */
    public function getMainIndexField()
    {
        throw new AdvError(AdvError::code_error, 'getMainIndexField 必须继承');
    }

    /**
     * 获取最后几个 主键值
     * @param bool $force_flush
     * @return array|mixed
     * @throws \Exception
     */
    public function getLastedPks($force_flush = false)
    {
        $res = '';
        $pk  = self::$pk;
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));
        if ($force_flush === false)
        {
            if (count(self::$last_pks) > 0)
            {
                return self::$last_pks;
            }
            $res = $this->getCache(self::cacheConfigKey_LastedPks, false, false);
        }
        if (empty($res) || $force_flush)
        {
            $models       = $this->findAllByWhere(['is_ok' => Opt::isOk]);
            self::$models = [];

            foreach ($models as $model)
            {
                self::$last_pks[]          = $model->$pk;
                self::$models[$model->$pk] = $model;
                $this->setCache(self::cacheConfigKey_Info, ['pk' => $model->$pk], $model->getOuterDataArray());

            }
            Sys::app()->redis('cache')->set(Sys::app()->params['cache_cfg'][self::cacheConfigKey_LastedPks]['key'], json_encode(self::$last_pks));
        }
        else
        {
            self::$last_pks = json_decode($res, true);
        }
        return self::$last_pks;


    }

    /**
     * @param $pk
     * @param bool $force_flush
     * @return ORM|static|false
     * @throws AdvError
     */
    public function getModelByPk($pk, $force_flush = false)
    {
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));

        if (isset(self::$models[$pk]) && $force_flush === false)
        {
            return self::$models[$pk];
        }
        $res = $force_flush ? [] : $this->getCache(self::cacheConfigKey_Info, ['pk' => $pk]);

        if ($force_flush || empty($res))
        {// 有没有key 和   val 是空值 是两码事
            $dao = $this->findOneByWhere([self::$pk => $pk]);
            if (empty($dao))
            {
                throw new AdvError(AdvError::res_not_exist, "不存在的道具:[{$pk}]");
            }
            self::$models[$pk] = $dao;
            $this->setCache(self::cacheConfigKey_Info, ['pk' => $pk], $dao->getOuterDataArray());
        }
        else
        {
            self::$models[$pk] = (new static())->loadData($res);
        }
        return self::$models[$pk];
    }


    /**
     * @param $field_val
     * @param bool $force_flush
     * @return ORM|static|false
     * @throws AdvError
     */
    public function getModelByMainIndex($field_val, $force_flush = false)
    {
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));

        if (isset(self::$models[$field_val]) && $force_flush === false)
        {
            return self::$models[$field_val];
        }
        $res = $force_flush ? [] : $this->getCache(self::cacheConfigKey_Info, ['pk' => $field_val]);

        if ($force_flush || empty($res))
        {// 有没有key 和   val 是空值 是两码事
            $dao = $this->findOneByWhere([$this->getMainIndexField() => $field_val]);
            if (empty($dao))
            {
                throw new AdvError(AdvError::res_not_exist, "信息不存在:[{$field_val}]");
            }
            self::$models[$field_val] = $dao;
            $this->setCache(self::cacheConfigKey_Info, ['pk' => $field_val], $dao->getOuterDataArray());
        }
        else
        {
            self::$models[$field_val] = (new static())->loadData($res);
        }
        return self::$models[$field_val];
    }

    /**
     * @param bool $force_flush
     * @return static[]
     * @throws AdvError
     */
    public function getLastedModels($force_flush = false)
    {
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));

        if ($force_flush === false && count(self::$models) !== 0)
        {
            return self::$models;
        }

        if ($force_flush)
        {
            $this->getLastedPks(true);
        }
        else
        {
            $pks = $this->getLastedPks();
            if (count(self::$models) === 0)
            {
                //说明 getItemCodes 走的缓存，所以更新
                $params = [];
                foreach ($pks as $pk)
                {
                    $params[] = $this->getCacheKey(self::cacheConfigKey_Info, ['pk' => $pk]);
                }
                $jsons = Sys::app()->redis('cache')->mget($params);

                if (count($jsons) === count($pks) && !in_array(false, $jsons, true))
                {
                    foreach ($jsons as $json)
                    {
                        $data = json_decode($json, true);
                        if (empty($data) || !isset($data[self::$pk]))
                        {
                            return $this->getLastedModels(true);
                        }
                        self::$models[$data[self::$pk]] = (new static())->loadData($data);
                    }
                }
                else
                {
                    return $this->getLastedModels(true);
                }
            }
        }
        return self::$models;
    }


}
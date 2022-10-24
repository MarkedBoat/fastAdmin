<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserDataStatisDao;
use modules\bee_invasion\v1\model\TCache;


class UserDataStatis extends UserDataStatisDao
{
    use TCache;

    const cacheConfigKey_attrs = 'UserDataStatisAttrs';
    private static $statis_data_map = [];


    /**
     * @return static
     * @throws AdvError
     */
    public function verifyKeyProperties()
    {
        if (!($this->user_id && $this->item_code))
        {
            throw new AdvError(AdvError::code_error, 'UserDataStatis verifyKeyProperties 不通过');
        }
        return $this;
    }


    /**
     * 累加值
     * <br> !!!!!!!!! 只加正值，不管负的
     * @param $num
     * @return static
     * @throws AdvError|\Exception
     */
    public function addUp($num)
    {
        $table_name = $this->getTableName();
        Sys::app()->addLog($table_name, '\modules\bee_invasion\v1\model\user\UserDataStitis::addUp(new_value)');

        $sql = "update {$table_name} set `item_addup`=`item_addup`+:num where `user_id`=:user_id and `item_code`=:item_code";
        $this->getDbConnect()->setText($sql)->bindArray([
            ':user_id'   => $this->user_id,
            ':item_code' => $this->item_code,
            ':num'       => $num,
        ])->execute();
        $this->reloadAttrs();
        return $this;
    }


    /**
     * @param $new_value
     * @return static
     * @throws AdvError|\Exception
     */
    public function recordNewValue($new_value)
    {
        $table_name = $this->getTableName();
        Sys::app()->addLog([$table_name, $new_value], '\modules\bee_invasion\v1\model\user\UserDataStitis::recordNewValue(new_value)');

        $sql = "update {$table_name} set `item_value`=:new_value where `user_id`=:user_id and `item_code`=:item_code";
        if ($this->getDbConnect()->setText($sql)->bindArray([
            ':user_id'   => $this->user_id,
            ':item_code' => $this->item_code,
            ':new_value' => $new_value,
        ])->execute())
        {
            $this->reloadAttrs();
        }
        return $this;
    }

    /**
     * @param $new_max
     * @return static
     * @throws AdvError|\Exception
     */
    public function recordMax($new_max)
    {
        $table_name = $this->getTableName();
        Sys::app()->addLog([$table_name, $new_max], '\modules\bee_invasion\v1\model\user\UserDataStitis::recordMax(new_value)');

        $sql = "update {$table_name} set `item_max`=:new_max where `user_id`=:user_id and `item_code`=:item_code and item_max>:new_max ";
        if ($this->getDbConnect()->setText($sql)->bindArray([
            ':user_id'   => $this->user_id,
            ':item_code' => $this->item_code,
            ':new_value' => $new_max,
        ])->execute())
        {
            $this->reloadAttrs();
        }

        return $this;
    }

    /**
     * @throws AdvError
     */
    public function reloadAttrs()
    {
        $dao = $this->findOneByWhere(['user_id' => $this->user_id, 'item_code' => $this->item_code], true);
        foreach (self::$field_config as $key => $config)
        {
            $this->$key = $dao->$key;
        }
        self::$statis_data_map[$this->user_id . '_' . $this->item_code] = $this;
        $this->setCache(self::cacheConfigKey_attrs, ['user_id' => $this->user_id, 'item_code' => $this->item_code], $dao->getOuterDataArray());

    }

    /**
     * @param int $user_id
     * @param string $item_code
     * @param bool $force_flush
     * @return static
     * @throws AdvError
     */
    public function getStatis($user_id, $item_code, $force_flush = false)
    {
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));

        if (empty($item_code))
        {
            if (empty($this->item_code))
            {
                throw new AdvError(AdvError::code_error, 'item_code全为空');
            }
            $item_code = $this->item_code;
        }
        else
        {
            $this->item_code = $item_code;
        }
        if ($force_flush === false && isset(self::$statis_data_map[$user_id . '_' . $item_code]))
        {
            return self::$statis_data_map[$user_id . '_' . $item_code];
        }

        if ($force_flush)
        {
            self::$statis_data_map = [];
            $dao                   = self::model()->findOneByWhere(['user_id' => $user_id, 'item_code' => $item_code], false);
            if (empty($dao))
            {
                $model            = new static();
                $model->user_id   = $user_id;
                $model->item_code = $item_code;
                $model->is_ok     = Opt::isOk;
                $model->insert(false);
                $dao = $model;
            }

            if (empty($dao))
            {
                throw new AdvError(AdvError::data_not_exist, '信息异常，查找不到数据');
            }

            self::$statis_data_map[$dao->user_id . '_' . $dao->item_code] = $dao;
            $this->setCache(self::cacheConfigKey_attrs, ['user_id' => $dao->user_id, 'item_code' => $dao->item_code], $dao->getOuterDataArray());
            Sys::app()->addLog([$dao, self::$statis_data_map[$dao->user_id . '_' . $dao->item_code]], 'xxxxx');
        }
        else
        {
            if (!isset(self::$statis_data_map[$user_id . '_' . $item_code]))
            {
                //说明 getItemCodes 走的缓存，所以更新

                $res = $this->getCache(self::cacheConfigKey_attrs, ['user_id' => $this->user_id, 'item_code' => $item_code]);

                if (empty($res))
                {
                    return $this->getStatis($user_id, $item_code, true);
                }
                else
                {
                    if (!isset($res['item_code']))
                    {
                        return $this->getStatis($user_id, $item_code, true);
                    }
                    self::$statis_data_map[$res['user_id'] . '_' . $res['item_code']] = $this->loadData($res);
                }
            }
        }
        if (!isset(self::$statis_data_map[$user_id . '_' . $item_code]) || empty(self::$statis_data_map[$user_id . '_' . $item_code]))
        {
            throw new AdvError(AdvError::res_not_exist, '查不到信息', [$user_id, $item_code, self::$statis_data_map]);
        }
        return self::$statis_data_map[$user_id . '_' . $item_code];
    }


    public function getOpenInfo()
    {
        return [
            'item_code'  => $this->item_code,
            'addup'      => $this->item_addup,
            'last_value' => $this->item_value,
            'max_value'  => $this->item_max,
        ];
    }

}
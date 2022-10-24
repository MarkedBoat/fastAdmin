<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\db\ORM;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\Note;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TItem;

/**
 * Trait TUserAccount
 * @package modules\bee_invasion\v1\model\user
 * @property int $user_id
 * @property string item_code
 * @property int|string item_amount
 */
Trait TUserAccount
{
    use TCache;

    public $itemValue;

    /**
     * @var $user User
     */
    protected $user;
    /**
     * @var $itemModel TItem
     */
    protected $itemModel; //对应的 item模型，通货？消耗性道具？凭据？
    // protected $valueType; //值类型
    //protected $valueField;//db table存储值的字段


    abstract public function initItemModel();


    /**
     * @param User $user
     * @return static
     */
    public function setUser(User $user)
    {
        $this->user    = $user;
        $this->user_id = $user->id;
        return $this;
    }

    /**
     * @param TItem | Note| Currency| ConsumableGoods $item_model
     * @return static
     */
    public function setItem($item_model)
    {
        $this->itemModel = $item_model;
        return $this;
    }


    /**
     *
     * @return mixed
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /****************************************************************************
     *
     *
     * 需要覆盖重写的不菲
     *
     ****************************************************************************/

    /**
     * 获取 记账类型   amount:计数  value:记录值变更
     * @return string
     */
    public function getAccountType()
    {
        return 'amount';
    }

    /**
     * 获取 记录user的字段  [user_id,role_id]
     * @return string
     */
    public function getUserField()
    {
        return 'user_id';
    }

    /**
     * 获取 记录code的字段  [item_code, equipment_code ,note_code]
     * @return string
     */
    public function getCodeField()
    {
        return 'item_code';
    }

    /**
     * 获取 记录数量的字段
     * @return string
     */
    public function getAmountField()
    {
        return 'item_amount';
    }

    /**
     * 获取 记录 value 的字段
     * @return string
     */
    public function getValueField()
    {
        return 'item_value';
    }


    /**
     *  获取 记录 value 的字段
     * @return string
     */
    public function getOldValueField()
    {
        return 'item_value';
    }

    /**
     *  获取 记录 value 的字段
     * @return string
     */
    public function getNewValueField()
    {
        return 'item_value';
    }

    /**
     * @return string
     */
    abstract function getUserChangeCodes();

    /**
     * 复制专有属性
     * @param TUserAccount $old_case 来源
     * @param TUserAccount $new_case 将会倍复制的
     */
    public function copyProperAttrs($old_case, $new_case)
    {
        $new_case->itemModel = $old_case->itemModel;
        $new_case->user      = $old_case->user;
    }


    /**
     * @return static
     * @throws AdvError
     */
    public function verifyKeyProperties()
    {
        if (!($this->user_id && $this->item_code))
        {
            throw new AdvError(AdvError::code_error, 'TUserAccount verifyKeyProperties 不通过');
        }
        return $this;
    }


    /**
     * @param $new_value
     * @return static
     * @throws AdvError|\Exception
     */
    public function updateAmount($new_value)
    {
        $table_name = $this->getTableName();
        Sys::app()->addLog($table_name, '\modules\bee_invasion\v1\model\user\TUserAccount::updateAmount(new_value)');
        if (empty($this->itemValue))
        {
            $sql = "insert ignore into {$table_name} set `user_id`=:user_id,`item_code`=:item_code,`item_amount`=:new_value,`is_ok`=:is_ok,`update_time`=now() on duplicate key update `item_amount`=`item_amount`+:new_value";
            $this->getDbConnect()->setText($sql)->bindArray([
                ':user_id'   => $this->user->id,
                ':item_code' => $this->item_code,
                ':new_value' => $new_value,
                ':is_ok'     => Opt::isOk,
            ])->execute();
        }
        else
        {
            $sql = "update {$table_name} set `item_amount`=`item_amount`+:new_value where `user_id`=:user_id and `item_code`=:item_code";
            $this->getDbConnect()->setText($sql)->bindArray([
                ':user_id'   => $this->user_id,
                ':item_code' => $this->item_code,
                ':new_value' => $new_value,
            ])->execute();
        }
        // $this->getAccount(true)->setUser($this->user)->setItemModel($this->getItemModel());
        $this->reloadDbData();
        $this->user->addChangedCode($this->getUserChangeCodes());
        if ($new_value > 0)
        {
            UserDataStatis::model()->getStatis($this->user_id, $this->item_code)->addUp($new_value);
        }
        return $this;
    }


    /**
     * @param $new_value
     * @return static
     * @throws AdvError|\Exception
     */
    public function updateValue($new_value)
    {
        $table_name = $this->getTableName();
        Sys::app()->addLog([$table_name, $new_value], '\modules\bee_invasion\v1\model\user\TUserAccount::updateValue(new_value)');
        if (empty($this->item_value))
        {
            $sql = "insert ignore into {$table_name} set `user_id`=:user_id,`item_status`=:item_status,`item_code`=:item_code,`item_value`=:new_value,`is_ok`=:is_ok,`update_time`=now() on duplicate key update `item_value`=:new_value";
            $this->getDbConnect()->setText($sql)->bindArray([
                ':user_id'     => $this->user->id,
                ':item_status' => 1,
                ':item_code'   => $this->item_code,
                ':new_value'   => $new_value,
                ':is_ok'       => Opt::isOk,
            ])->execute();
        }
        else
        {
            $sql = "update {$table_name} set `item_status`=:item_status,`item_value`=:new_value where `user_id`=:user_id and `item_code`=:item_code";
            $this->getDbConnect()->setText($sql)->bindArray([
                ':user_id'     => $this->user_id,
                ':item_status' => 1,
                ':item_code'   => $this->item_code,
                ':new_value'   => $new_value,
            ])->execute();
        }
        $this->reloadDbData();
        $this->user->addChangedCode($this->getUserChangeCodes());
        return $this;
    }

    public function reloadDbData()
    {
        $dao = $this->findOneByWhere(['user_id' => $this->user_id, 'item_code' => $this->item_code], true);
        foreach (self::$field_config as $key => $config)
        {
            $this->$key = $dao->$key;
        }
        self::$account_info_map[$this->user_id . '_' . $this->item_code] = $this;
        $this->setCache(self::cacheConfigKey_accountInfo, ['user_id' => $this->user_id, 'item_code' => $this->item_code], $dao->getOuterDataArray());

    }

    /**
     * @param string $item_code
     * @param bool $force_flush
     * @return static
     * @throws AdvError
     */
    public function getAccount($item_code = '', $force_flush = false)
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
        if ($force_flush === false && isset(self::$account_info_map[$this->user_id . '_' . $item_code]))
        {
            return self::$account_info_map[$this->user_id . '_' . $item_code];
        }

        if ($force_flush)
        {
            self::$account_info_map = [];
            $dao                    = self::model()->findOneByWhere(['user_id' => $this->user_id, 'item_code' => $item_code], false);
            if (empty($dao))
            {
                $model            = new static();
                $model->user_id   = $this->user_id;
                $model->item_code = $item_code;
                $model->is_ok     = Opt::isOk;
                $model->insert(false);
                $dao = $model;
            }

            if (empty($dao))
            {
                throw new AdvError(AdvError::data_not_exist, '信息异常，查找不到数据');
            }

            $this->copyProperAttrs($this, $dao);
            self::$account_info_map[$this->user_id . '_' . $dao->item_code] = $dao;
            $this->setCache(self::cacheConfigKey_accountInfo, ['user_id' => $this->user_id, 'item_code' => $dao->item_code], $dao->getOuterDataArray());
            Sys::app()->addLog([$dao, self::$account_info_map[$this->user_id . '_' . $dao->item_code]], 'xxxxx');
        }
        else
        {
            if (!isset(self::$account_info_map[$this->user_id . '_' . $item_code]))
            {
                //说明 getItemCodes 走的缓存，所以更新

                $res = $this->getCache(self::cacheConfigKey_accountInfo, ['user_id' => $this->user_id, 'item_code' => $item_code]);

                if (empty($res))
                {
                    return $this->getAccount($item_code, true);
                }
                else
                {
                    if (!isset($res['item_code']))
                    {
                        return $this->getAccount($item_code, true);
                    }
                    self::$account_info_map[$this->user_id . '_' . $res['item_code']] = $this->loadData($res);
                }
            }
        }
        if (!isset(self::$account_info_map[$this->user_id . '_' . $item_code]) || empty(self::$account_info_map[$this->user_id . '_' . $item_code]))
        {
            throw new AdvError(AdvError::res_not_exist, '查不到信息', [$item_code, self::$account_info_map[$this->user_id . '_' . $item_code]]);
        }
        return self::$account_info_map[$this->user_id . '_' . $item_code];
    }

    /**
     * @param array $item_codes
     * @param bool $force_flush
     * @return static[]
     * @throws AdvError
     */
    public function getAccounts($item_codes, $force_flush = false)
    {
        $force_flush = $force_flush || !empty(Sys::app()->getOptValue('no_cache'));

        if ($force_flush === false && count(self::$account_info_map) === count($item_codes))
        {
            return self::$account_info_map;
        }

        if ($force_flush)
        {
            self::$account_info_map = [];
            $daos                   = $this->findAllByWhere(['user_id' => $this->user_id], false);
            $mset_array             = [];
            foreach ($daos as $dao)
            {
                self::$account_info_map[$this->user_id . '_' . $dao->item_code] = $dao;

                $mset_array[$this->getCacheKey(self::cacheConfigKey_accountInfo, ['user_id' => $this->user_id, 'item_code' => $dao->item_code])] = $dao;
            }
            $this->mset($mset_array);
        }
        else
        {
            if (count(self::$account_info_map) === 0)
            {
                //说明 getItemCodes 走的缓存，所以更新
                $keys = [];
                foreach ($item_codes as $item_code)
                {
                    $keys[] = $this->getCacheKey(self::cacheConfigKey_accountInfo, ['user_id' => $this->user_id, 'item_code' => $item_code]);
                }
                $jsons = $this->mget($keys);

                if (count($jsons) === count($item_codes) && !in_array(false, $jsons, true))
                {
                    foreach ($jsons as $json)
                    {
                        $data = json_decode($json, true);
                        if (empty($data) || !isset($data['item_code']))
                        {
                            return $this->getAccounts($item_codes, true);
                        }
                        self::$account_info_map[$this->user_id . '_' . $data['item_code']] = (new static())->loadData($data);
                    }
                }
                else
                {
                    return $this->getAccounts($item_codes, true);
                }
            }
        }
        return self::$account_info_map;
    }


}
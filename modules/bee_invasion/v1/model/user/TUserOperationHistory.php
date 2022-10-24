<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\error\AdvError;
use models\common\opt\Opt;

use models\common\sys\Sys;
use modules\bee_invasion\v1\model\TItem;
use modules\bee_invasion\v1\model\user\TUserAccount;
use modules\bee_invasion\v1\model\user\User;

/**
 * @date 2022/8/13 16:25
 * @author user0558@qq.com (automatic generation,maybe not creater)
 * @example
 * @link
 * @desc
 * Class BaseUserOperationHistory
 * @package modules\bee_invasion\v1\model\user
 * <br>
 * <br> 用户操作 行为 基类
 * <br>
 */
Trait TUserOperationHistory
{

    /**
     * @var User
     */
    private $user;
    /**
     * @var $userAccountModel TUserAccount
     */
    private $userAccountModel;
    /**
     * @var $itemModel TItem
     */
    private $itemModel;
    private $newValue;

    /**
     * @param TUserAccount $userAccount
     * @return static
     * @throws
     */
    public function setUserAccountModel($userAccount)
    {
        $userAccount->verifyKeyProperties();
        $this->userAccountModel = $userAccount;
        $this->user_id          = $userAccount->user_id;
        $this->item_code        = $userAccount->item_code;
        return $this;

    }


    /**
     * 设置操作步骤
     * @param int $step_number
     * @return  static
     */
    public function setOperationStep($step_number)
    {
        $this->src_op_step = $step_number;
        return $this;
    }


    /**
     * 记录一个标准的 账号 记录
     * @param string $source
     * @param string $unique_id
     * @param int|string $item_value
     * @return static
     * @throws AdvError
     */
    public function tryRecord($source, $unique_id, $item_value)
    {
        Sys::app()->addLog([$source, $unique_id, $item_value], 'tryRecord');
        if (!isset(self::src_map[$source]))
        {
            throw new AdvError(AdvError::res_not_exist, "操作类型不存在:{$source}");
        }
        $this->src         = self::src_map[$source]['val'];
        $this->src_op_type = self::src_map[$source]['op_type'];
        $this->src_id      = $unique_id;
        $this->item_code   = $this->userAccountModel->item_code;
        $this->update_time = date('Y-m-d H:i:s');

        $account_type = $this->userAccountModel->getAccountType();
        if ($account_type === Opt::valueType_amount)
        {
            return $this->recordStandardAmountOperation($source, $unique_id, $item_value);
        }
        else if ($account_type === Opt::valueType_value)
        {
            return $this->recordStandardValueOperation($source, $unique_id, $item_value);
        }
        else
        {
            throw new AdvError(AdvError::data_info_unexpected, "调用错误，不能记录", [$account_type]);
        }
    }


    /**
     * 记录一个标准的 账号 记录
     * @param string $source
     * @param string $unique_id
     * @param int|string $item_value
     * @return static
     * @throws AdvError
     */
    public function recordStandardAmountOperation($source, $unique_id, $item_value = 1)
    {
        if ($this->userAccountModel->getAccountType() !== Opt::valueType_amount)
        {
            throw new AdvError(AdvError::data_info_unexpected, "调用错误，不能记录", [$this->userAccountModel->getAccountType(), Opt::valueType_amount]);
        }
        if (empty($item_value))
        {
            throw new AdvError(AdvError::data_info_unexpected, "记录数值，不能为空", [$item_value]);
        }


        $this->item_amount   = intval($this->src_op_type) === 1 ? $item_value : (-$item_value);
        $this->curr_amount   = $this->userAccountModel->item_amount;
        $this->expect_amount = bcadd($this->curr_amount, $this->item_amount);

        $try_insert_res = $this->insert(false);
        if ($try_insert_res === false)
        {
            return false;
            //throw new AdvError(AdvError::db_common_error, '记录历史失败');
        }
        $this->userAccountModel->updateAmount($this->item_amount);
        return $this;
    }

    /**
     * 记录一个标准的 值变换 记录
     * @param string $source
     * @param string $unique_id
     * @param int|string $new_value
     * @return static
     * @throws AdvError
     */
    public function recordStandardValueOperation($source, $unique_id, $new_value)
    {
        if ($this->userAccountModel->getAccountType() !== Opt::valueType_value)
        {
            throw new AdvError(AdvError::data_info_unexpected, "调用错误，不能记录", [$this->userAccountModel->getAccountType(), Opt::valueType_value]);
        }

        $this->new_value = $new_value;
        $this->old_value = $this->userAccountModel->item_value;
        $try_insert_res  = $this->insert(false);
        if ($try_insert_res === false)
        {
            return false;
            //throw new AdvError(AdvError::db_common_error, '记录历史失败');
        }

        $this->userAccountModel->updateValue($this->new_value);

        return $this;
    }


}
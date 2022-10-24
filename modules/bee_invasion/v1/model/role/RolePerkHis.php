<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCg;

class RolePerkHis extends UserCgHisDao
{

    const src_map = [
        'used' => ['val' => 1, 'op_type' => 1,],
    ];
    const srcUsed = 'used';


    /**
     * @var User
     */
    private $user;
    private $operationSource;
    private $operationUniqueId;
    private $operationType;


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
     * @param string $item_code
     * @param int $item_amount
     * <br>
     * <br>   !!!!!!!!!!!!! 使用前,应先设置 operation
     * <br>
     * @return static
     * @throws AdvError
     */
    public function setItem($item_code, $item_amount)
    {

        if (!in_array($item_code, (new Perk())->getItemCodes(), true))
        {
            throw new AdvError(AdvError::res_not_exist, "道具不存在:{$item_code} ");
        }
        $item_amount         = intval($this->src_op_type) === 1 ? $item_amount : (-$item_amount);
        $this->item_code     = $item_code;
        $this->item_amount   = $item_amount;
        $curr_amount         = RolePerk::getGoodsAmount($this->user, $item_code);
        $this->curr_amount   = $curr_amount;
        $this->expect_amount = $curr_amount + $item_amount;
        return $this;
    }


    /**
     * @param string $source
     * @param string $unique_id
     * @return static
     * @throws AdvError
     */
    public function setOperation($source, $unique_id)
    {
        if (!isset(self::src_map[$source]))
        {
            throw new AdvError(AdvError::res_not_exist, "操作类型不存在:{$source}");
        }
        $this->operationSource   = self::src_map[$source]['val'];
        $this->operationType     = self::src_map[$source]['op_type'];
        $this->operationUniqueId = $unique_id;

        $this->src         = self::src_map[$source]['val'];
        $this->src_op_type = self::src_map[$source]['op_type'];
        $this->src_id      = $unique_id;


        return $this;
    }


    /**
     * @return static|bool
     * @throws AdvError
     */
    public function recordHis()
    {
        $role_perk_dao     = RolePerkDao::model();
        $table_name        = $role_perk_dao->getTableName();
        $this->update_time = date('Y-m-d H:i:s');

        $try_insert_res = $this->insert(false);
        if ($try_insert_res === false)
        {
            throw new AdvError(AdvError::db_common_error, '写入失败');
        }
        if (intval($this->curr_amount))
        {
            $sql = "update {$table_name} set `used_times`=`used_times`+:item_amount where `role_id`=:role_id and `perk_item_code`=:perk_item_code";
            $role_perk_dao->getDbConnect()->setText($sql)->bindArray([
                ':role_id'        => $this->user_id,
                ':perk_item_code' => $this->item_code,
                ':item_amount'    => $this->item_amount,
            ])->execute();
        }
        else
        {
            $sql = "insert ignore into {$table_name} set `role_id`=:role_id,`perk_item_code`=:perk_item_code,`used_times`=:item_amount,`is_ok`=:is_ok,`update_time`=unix_timestamp() on duplicate key update `used_times`=`used_times`+:item_amount";
            $role_perk_dao->getDbConnect()->setText($sql)->bindArray([
                ':role_id'        => $this->user_id,
                ':perk_item_code' => $this->item_code,
                ':item_amount'    => $this->item_amount,
                ':is_ok'          => Opt::isOk,
            ])->execute();
        }
        RolePerk::getGoodsAmount($this->user, $this->item_code, true);
        return $this;
    }


}
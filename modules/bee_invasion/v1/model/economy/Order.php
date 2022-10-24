<?php

namespace modules\bee_invasion\v1\model\economy;


use models\common\db\ORM;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\economy\OrderBillDao;
use modules\bee_invasion\v1\dao\game\economy\OrderDao;
use modules\bee_invasion\v1\dao\game\economy\PriceListDao;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\dao\game\notice\NoticeDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\task\AsyncTask;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\TItem;
use modules\bee_invasion\v1\model\user\User;

class Order extends OrderDao
{
    use TInfo;


    const cacheConfigKey_LastedPks = 'OrderLastedPks';
    const cacheConfigKey_Info      = 'OrderItemInfo';


    private static $last_pks = [];
    private static $models   = [];

    /**
     * @var $bills OrderBillDao[]
     */
    private $bills = [];
    private $user;

    private $payList   = [];
    private $goodsList = [];

    /**
     * @param User $user
     * @return static
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param PriceItem $price_item
     * @param $amount
     * @return static
     */
    public function addGoodsBill(PriceItem $price_item, $amount)
    {
        $bill               = new OrderBillDao();
        $bill->goods_amount = $price_item->goods_item_amount * $amount;
        $bill->goods_code   = $price_item->goods_item_code;
        $bill->goods_class  = $price_item->goods_item_class;
        $bill->payment_code = $price_item->pay_item_code;
        $bill->price_detail = [$price_item->pay_item_amount, $price_item->goods_item_amount];
        $bill->bill_sum     = $price_item->pay_item_amount * $amount;
        $bill->update_time  = date('Y-m-d H:i:s', time());

        return $this;

    }

    public function countSum()
    {

        foreach ($this->bills as $bill)
        {

        }
    }


    public function addAsyncTask()
    {
        $task              = new AsyncTask();
        $task->op          = AsyncTask::opShoppingReward4Inviter;
        $task->op_flag     = $this->id;
        $task->op_param    = json_encode(['order_id' => $this->id, 'pay_amount' => $this->order_sum]);
        $task->is_complete = Opt::NOT;
        $task->is_ok       = Opt::YES;
        $task->insert(false, false);
        Sys::app()->addLog(['order_id' => $this->id], 'Order.addAsyncTask');
    }

}
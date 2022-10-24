<?php

namespace modules\bee_invasion\v1\model\task;


use models\common\db\ORM;
use modules\bee_invasion\v1\dao\game\NoteDao;
use modules\bee_invasion\v1\dao\game\notice\NoticeDao;
use modules\bee_invasion\v1\dao\task\AsyncTaskDao;
use modules\bee_invasion\v1\model\CItem;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\TItem;

class AsyncTask extends AsyncTaskDao
{

    const opShoppingReward4Inviter = 'shoppingReward4Inviter';//消费返现
    const opAdReward4Inviter       = 'adReward4Inviter';      //广告返现

    const isCompleteError = 3;

    public function getOpenInfo()
    {

        return [
            'id'          => intval($this->id),
            'op'          => $this->op,
            'op_flag'     => $this->op_flag,
            'op_mod'      => $this->op_mod,
            'is_ok'       => $this->is_ok,
            'is_complete' => $this->is_complete,
            'op_param'    => $this->getJsondecodedValue($this->op_param, 'array'),
        ];
    }


}
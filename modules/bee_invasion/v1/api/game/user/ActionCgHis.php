<?php

namespace modules\bee_invasion\v1\api\game\user;

use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\UserCgHis;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionCgHis extends GameBaseAction
{
    public function isUseApiCache()
    {
        return true;
    }

    public function getAcceptParamKeys()
    {
        return ['user_id', 'item_code', 'operation_type', 'page_index'];// 'page_size',
    }

    public function getInnerDataUpdateTimeKeys()
    {
        $list      = [];
        $item_code = $this->inputDataBox->tryGetString('item_code');
        if ($item_code)
        {
            $list[] = ApiCache::model()->getCacheKey('ChangeFlagUserCg', ['user_id' => $this->user->id]);
            //$list[] = 'user_currency_his_' . $this->user->id . '_' . $item_code;

        }
        return $list;

    }

    public function run()
    {
        $item_code = $this->inputDataBox->tryGetString('item_code');
        $op_type   = $this->inputDataBox->tryGetInt('operation_type');
        //$page_size  = $this->inputDataBox->getIntNotNull('page_size');
        $page_index = $this->inputDataBox->getIntNotNull('page_index');
        $page_size  = 20;
        $conditions = ['user_id' => $this->user->id];
        if ($item_code)
        {
            $conditions['item_code'] = $item_code;
        }
        if ($op_type && in_array($op_type, [1, 2]))
        {
            $conditions['src_op_type'] = $op_type;
        }
        $dao = UserCgHis::model()->addSort('id', 'desc');
        if ($page_size && $page_index)
        {
            $dao->setPage($page_index, $page_size)->setOptCountTotalStatus(true);

        }
        $daos              = $dao->findAllByWhere($conditions, false);
        $page_info         = $dao->getPageInfo();
        $page_info['list'] = array_map(function ($model) { return $model->getOpenInfo(); }, $daos);
        return $page_info;

    }
}
<?php

namespace modules\bee_invasion\v1\api\admin\game\user\reward\object;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCurrencyDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;
use modules\bee_invasion\v1\model\user\UserObjectHis;
use modules\bee_invasion\v1\model\user\UserRankAwardHis;


class ActionPostalInfoReview extends AdminBaseAction
{
    public function run()
    {
        //
        $object_his_id = $this->inputDataBox->getIntNotNull('object_his_id');
        $express_info  = $this->inputDataBox->getStringNotNull('express_info');
        $user_tel      = $this->inputDataBox->tryGetInt('user_tel');
        $user_name     = $this->inputDataBox->tryGetString('user_name');
        $user_addr     = $this->inputDataBox->tryGetString('user_addr');


        $object_his = UserObjectHis::model()->findByPk($object_his_id);
        //
        //rank_award
        $src_his_dao = false;
        if ($object_his->src === 'rank_award')
        {
            $src_his_dao = UserRankAwardHis::model()->findOneByWhere(['item_class' => 'object', 'his_id' => $object_his_id]);
            if (in_array($src_his_dao->award_status, [UserRankAwardHis::stepOk]))
            {
                return $this->dispatcher->createInterruption(AdvError::res_has_delivered['detail'], '排行榜奖励已经审核通过了，不能修改', false);
            }
        }
        else
        {
            return $this->dispatcher->createInterruption(AdvError::request_param_verify_fail['detail'], '不能识别', false);
        }


        if (!in_array($object_his->src_op_step, [UserObjectHis::stepWaitInfoCheck, UserObjectHis::stepPostalInfoFixed]))
        {
            return $this->dispatcher->createInterruption(AdvError::res_has_delivered['detail'], '还没填收获地址或者斧正收获地址,或者不能再次审核通过', false);
        }
        $old_open_info = $object_his->getOpenInfo();
        if ($user_tel)
        {
            $object_his->user_tel = $user_tel;
        }
        if ($user_name)
        {
            $object_his->user_name = $user_name;
        }
        if ($user_addr)
        {
            $object_his->user_addr = $user_addr;
        }

        $object_his->express_info = $express_info;
        $object_his->src_op_step  = UserObjectHis::stepOk;


        if ($object_his->src === 'rank_award')
        {
            $src_his_dao->award_status = UserObjectHis::stepOk;
            $src_his_dao->update();
        }
        $object_his->update();
        return ['old' => $old_open_info, 'now' => $object_his->getOpenInfo()];

    }
}
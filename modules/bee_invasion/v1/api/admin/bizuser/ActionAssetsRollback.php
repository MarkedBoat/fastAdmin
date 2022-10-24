<?php

namespace modules\bee_invasion\v1\api\admin\bizuser;

use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\admin\AdminBaseAction;
use modules\bee_invasion\v1\dao\admin\bizuser\AssetsOpLog;
use modules\bee_invasion\v1\dao\user\UserFakeDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;

class ActionAssetsRollback extends AdminBaseAction
{


    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);

        $his_id     = $this->inputDataBox->getIntNotNull('his_id');
        $item_class = $this->inputDataBox->getStringNotNull('item_class');
        $reason     = $this->inputDataBox->getStringNotNull('reason');
        if (!in_array($item_class, ['currency']))
        {
            throw new AdvError(AdvError::request_param_verify_fail, '资产类型没有对应的:' . $item_class);
        }

        $res = [
            'log'         => false,
            'old_his'     => false,
            'new_his_res' => false,
            'new_his'     => false
        ];
        if ('currency' === $item_class)
        {

            $item_class = $this->inputDataBox->getStringNotNull('item_class');

            $log = new AssetsOpLog();


            $old_his_dao = UserCurrencyHis::model()->findByPk($his_id);
            if ($old_his_dao->is_rollback === Opt::YES)
            {
                throw new AdvError(AdvError::data_info_unexpected, '已经被回滚过了:' . $item_class);

            }
            if ($old_his_dao->src === UserCurrencyHis::srcErrorDataRollback)
            {
                throw new AdvError(AdvError::data_info_unexpected, '这是一条回滚记录，不能进行回滚');
            }
            if ($old_his_dao->src_op_type === 2)
            {
                throw new AdvError(AdvError::data_info_unexpected, '这是一条 减操作 不能进行回滚');
            }

            $biz_user     = User::model()->findByPk($old_his_dao->user_id);
            $user_account = UserCurrency::model()->setUser($biz_user)->getAccount($old_his_dao->item_code);


            $log->op_admin  = $this->user->id;
            $log->op_flag   = "{$item_class}/{$old_his_dao->item_code} {$old_his_dao->src}/{$old_his_dao->src_id}  {$old_his_dao->id}";
            $log->op_detail = "撤回:{$item_class}/{$old_his_dao->item_code} src:{$old_his_dao->src}/{$old_his_dao->src_id} id:{$old_his_dao->id} 理由:{$reason}";;
            $log->biz_user_id = $biz_user->id;
            $log->item_class  = $item_class;
            $log->item_code   = $old_his_dao->item_code;
            $log->item_amount = $old_his_dao->item_amount;
            $log->op_type     = 2;
            $log->is_complete = Opt::NOT;

            $log_res    = $log->insert(true, true);
            $res['log'] = $log_res->getOuterDataArray();
            if ($log_res === false)
            {
                throw new AdvError(AdvError::db_save_error, '保存错误，请检查理由是否重复:' . $log->op_flag);
            }


            $new_his = (new UserCurrencyHis())->setUserAccountModel($user_account)->setOperationStep(1);

            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $biz_user->id], time());
            $user_account->verifyKeyProperties();
            $new_his->src_remark = json_encode(['old_his_id' => $old_his_dao->id, 'old_src' => $old_his_dao->src, 'old_src_id' => $old_his_dao->src_id, 'reason' => $reason], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_UNICODE);
            $new_his->setOperationStep(1);
            $res['new_his_res']       = $new_his->tryRecord(UserCurrencyHis::srcErrorDataRollback, $old_his_dao->id, $old_his_dao->item_amount);
            $res['new_his']           = $new_his->getOpenInfo();
            $old_his_dao->is_rollback = Opt::YES;
            $old_his_dao->src_id      = 'RB_' . $old_his_dao->src_id;
            $old_his_dao->src_remark  = json_encode(['new_his_id' => $new_his->id, 'rollback_reason' => $reason], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_UNICODE);
            $old_his_dao->update();
            $res['old_his']   = $old_his_dao->getOpenInfo();
            $log->is_complete = Opt::YES;
            $log->update(true, true);
            $res['log'] = $log_res->getOuterDataArray();

        }

        return $res;


    }


}
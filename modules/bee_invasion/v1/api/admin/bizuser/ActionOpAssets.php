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

class ActionOpAssets extends AdminBaseAction
{


    public function run()
    {
        //  $this->dispatcher->setOutType(Api::outTypeText);

        $op_flag     = $this->inputDataBox->getStringNotNull('op_flag');
        $op_detail   = $this->inputDataBox->getStringNotNull('op_detail');
        $biz_user_id = $this->inputDataBox->getIntNotNull('biz_user_id');
        $item_class  = $this->inputDataBox->getStringNotNull('item_class');
        $item_code   = $this->inputDataBox->getStringNotNull('item_code');
        $item_amount = $this->inputDataBox->getIntNotNull('item_amount');

        $log              = new AssetsOpLog();
        $log->op_admin    = $this->user->id;
        $log->op_flag     = $op_flag;
        $log->op_detail   = $op_detail;
        $log->biz_user_id = $biz_user_id;
        $log->item_class  = $item_class;
        $log->item_code   = $item_code;
        $log->item_amount = $item_amount;
        $log->op_type     = $item_amount > 0 ? 1 : 2;
        $log->is_complete = Opt::NOT;

        if (!in_array($item_class, ['currency', 'cg', 'equipment', 'object']))
        {
            throw new AdvError(AdvError::request_param_verify_fail, '资产类型没有对应的:' . $item_class);
        }
        $biz_user = User::model()->findByPk($biz_user_id, false);
        if (empty($biz_user))
        {
            throw new AdvError(AdvError::res_not_exist, '业务用户不存在:' . $biz_user_id);
        }
        $log_res = $log->insert(true, true);
        if ($log_res === false)
        {
            throw new AdvError(AdvError::db_save_error, '保存错误，请检查理由是否重复:' . $op_flag);
        }

        $res         = [
            'log'     => $log->getOuterDataArray(),
            'his_res' => false,
            'his'     => false
        ];
        $item_amount = abs($item_amount);
        if ('currency' === $item_class)
        {

            $user_account = UserCurrency::model()->setUser($biz_user)->getAccount($item_code);
            $goods_his    = (new UserCurrencyHis())->setUserAccountModel($user_account)->setOperationStep(1);
            ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $biz_user_id], time());
            $user_account->verifyKeyProperties();
            $goods_his->setOperationStep(1);
            $res['his_res'] = $goods_his->tryRecord($log->op_type === 1 ? UserCurrencyHis::srcAdminAdd : UserCurrencyHis::srcAdminCutdown, $log->id, $item_amount);
            $res['his']     = $goods_his->getOpenInfo();

        }
        return $res;


    }


}
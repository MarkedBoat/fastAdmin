<?php

namespace modules\bee_invasion\v1\api\com;


use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\user\OpenCodeDao;


class ActionGetUserOpenCode extends GameBaseAction
{
    public $requestMethods = ['POST'];

    public function run()
    {

        $partner_code             = $this->inputDataBox->getStringNotNull('partner_code');
        $code_model               = new OpenCodeDao();
        $code_model->user_id      = $this->user->id;
        $code_model->partner_code = $partner_code;
        $code_model->open_code    = md5($partner_code . $code_model->user_id . time());
        $code_model->setOnDuplicateKeyUpdate([
            'open_code' => $code_model->open_code,
            'is_used'   => Opt::NOT
        ]);
        $res = $code_model->insert(false, false);
        if ($res)
        {
            return ['code' => $code_model->open_code];
        }
        else
        {
            return $this->dispatcher->createInterruption(AdvError::db_save_error['detail'], '失败，清重试', false);
        }
    }
}
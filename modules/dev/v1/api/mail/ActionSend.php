<?php

namespace modules\dev\v1\api\mail;

use models\common\ActionBase;
use models\ext\mail\TencentMailSmtp;


class ActionSend extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public function __construct($param = [])
    {
        parent::init($param);
    }


    public function run()
    {
        $mailer = new TencentMailSmtp();
        return $mailer->sendMail($this->params->getStringNotNull('title'), $this->params->getStringNotNull('content'), $this->params->tryGetString('file'));

    }

}
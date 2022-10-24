<?php

namespace modules\bee_invasion\v1\api\game\config;

use models\common\ActionBase;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\notice\Notice;


class ActionInfo extends ActionBase
{
    public function run()
    {
        $code         = $this->inputDataBox->getStringNotNull('item_code');
        $config_model = Config::model()->getItemByCode($code);
        return $config_model->getOpenInfo();
    }
}
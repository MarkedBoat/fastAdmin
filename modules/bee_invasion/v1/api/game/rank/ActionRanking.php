<?php

namespace modules\bee_invasion\v1\api\game\rank;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\user\UserLoginTokenDao;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\play\Perk;


class ActionRanking extends GameBaseAction
{
    public function run()
    {
        $ymd          = date('Ymd', time());
        $channel_code = $this->inputDataBox->getStringNotNull('channel_code');
        return RankTop::model()->getRankInfo($channel_code, $ymd, $this->user->id);

    }
}
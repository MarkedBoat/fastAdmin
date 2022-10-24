<?php

namespace modules\bee_invasion\v1\api\game\ad;

use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\role\RoleNote;
use modules\bee_invasion\v1\model\user\UserAd;


class ActionGetAd extends GameBaseAction
{
    public function run()
    {

        $item_code = $this->inputDataBox->getStringNotNull('item_code');
        $ttl       = 300;
        $user_ad   = UserAd::model($this->user);
        //  $code      = RoleNote::generateNoteCode($this->user->id, $item_code, $ttl);
        //$res       = RoleNote::verifyNoteCode($this->user->id, $item_code, $code);
        return $user_ad->generateAdNoteCode($item_code, $ttl);


    }


}
<?php

namespace modules\bee_invasion\v1\api\game\ad;

use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\CgDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\role\RoleNote;


class ActionReport extends GameBaseAction
{
    public function run()
    {

        $note_code = $this->inputDataBox->getStringNotNull('note_code');
        $item_code = $this->inputDataBox->getStringNotNull('item_code');

        $res = RoleNote::verifyNoteCode($this->user->id, $item_code, $note_code);
        if (in_array(0, $res, true))
        {
            throw  new AdvError(AdvError::request_param_verify_fail, '凭证无效，可能是过期了，请重新获取');
        }

        $config_model = new Config();

        $curr_ad_flag = $config_model->getCache('UserAdFlag', ['user_id' => $this->user->id]);
        if ($curr_ad_flag !== $note_code)
        {
            throw  new AdvError(AdvError::request_param_verify_fail, '凭证无效，请重新获取');
        }





        return ['ad' => ['note' => ['ttl' => $ttl, 'code' => $code, $res]]];

    }


}
<?php

namespace modules\bee_invasion\v1\api\game\rank;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\game\PerkDao;
use modules\bee_invasion\v1\dao\user\UserLoginTokenDao;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\RankTop;
use modules\bee_invasion\v1\model\play\Perk;


class ActionTop extends ActionBase
{
    public function run()
    {
        $user_id      = false;
        $token        = $this->inputDataBox->tryGetString('user_token');
        $channel_code = $this->inputDataBox->getStringNotNull('channel_code');
        if ($token)
        {
            $user_token = UserLoginTokenDao::model()->findOneByWhere(['user_token' => $token], false);
            if (!empty($user_token))
            {
                $user_id = $user_token->user_id;
            }
        }
        $channel_model = Channel::model()->getItemByCode($channel_code);

        if ($channel_model->service_status === Opt::NOT)
        {
            return ['rank_update_date' => date('Ymd', time() - 86400 * 2), 'user_rank_info' => false, 'list' => []];
        }

        $ymd = date('Ymd', time() - 86400);


        $update_date = date('Y-m-d 01:00:00', time());
        $user_rank   = RankTop::model()->findOneByWhere(['channel_code' => $channel_code, 'user_id' => $user_id, 'date_index' => $ymd], false);
        $list        = (new RankTop())->getList($channel_code, $ymd);
        if (empty($list))
        {
            for ($i = 0; $i < 5; $i++)
            {
                $ymd2      = date('Ymd', time() - 86400 * ($i + 2));
                $user_rank = RankTop::model()->findOneByWhere(['channel_code' => $channel_code, 'user_id' => $user_id, 'date_index' => $ymd2], false);
                $list      = (new RankTop())->getList($channel_code, $ymd2);
                if ($list)
                {
                    $update_date = date('Y-m-d 01:00:00', time() - 86400 * ($i + 1));
                    break;
                }
            }

        }

        return ['rank_update_date' => $update_date, 'user_rank_info' => empty($user_rank) ? false : $user_rank, 'list' => array_values($list)];
    }
}
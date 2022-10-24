<?php

namespace modules\bee_invasion\v1\api\open\user;

use models\common\ActionBase;
use models\common\opt\Opt;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\api\open\OpenBaseAction;
use modules\bee_invasion\v1\dao\user\OpenCodeDao;
use modules\bee_invasion\v1\model\user\User;


class ActionCode2info extends OpenBaseAction
{

    public function run()
    {
        $code = $this->inputDataBox->getStringNotNull('code');
        if ($code === 'test')
        {
            $user = (new User())->findByPk(1);
            return [
                'user' => [
                    'uuid'        => intval($user->id),
                    'nickname'    => $user->nickname,
                    'avatar'      => $user->avatar,
                    'mobile'      => intval($user->mobile),
                    'reg_time'    => $user->reg_time,
                    'create_time' => $user->create_time,
                    'update_time' => $user->update_time,
                ]
            ];
        }
        else
        {

            $model = OpenCodeDao::model()->findOneByWhere(['partner_code' => $this->partner->src_code, 'open_code' => $code], false);
            if (empty($model))
            {
                return $this->dispatcher->createInterruption('code_invalid', '无效的code', ['code' => $code]);
            }
            if ($model->is_used === Opt::YES)
            {
                return $this->dispatcher->createInterruption('code_invalid', '无效的code', ['code' => $code]);
            }
            $ts = strtotime($model->update_time);
            if (($ts + 300) < time())
            {
                return $this->dispatcher->createInterruption('code_invalid', '无效的code', ['code' => $code]);
            }
            $model->is_used = Opt::YES;
            $model->update();
            $user = (new User())->findByPk($model->user_id);
            return [
                'user' => [
                    'uuid'        => intval($user->id),
                    'nickname'    => $user->nickname,
                    'avatar'      => $user->avatar,
                    'mobile'      => intval($user->mobile),
                    'reg_time'    => $user->reg_time,
                    'create_time' => $user->create_time,
                    'update_time' => $user->update_time,
                ]
            ];
        }


    }
}
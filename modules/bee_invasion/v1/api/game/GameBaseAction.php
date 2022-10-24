<?php

namespace modules\bee_invasion\v1\api\game;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserLoginTokenDao;
use modules\bee_invasion\v1\model\user\User;


abstract class GameBaseAction extends ActionBase
{
    public $dataSource = 'POST';
    /**
     * @var User
     */
    protected $user;


    public function init()
    {
        parent::init();
        $token = $this->inputDataBox->tryGetString('user_token');
        if (!$token)
        {
            throw new AdvError(AdvError::user_token_not_exist);
        }
        $user_token = UserLoginTokenDao::model()->findOneByWhere(['user_token' => $token], false);
        if (empty($user_token))
        {
            throw new AdvError(AdvError::user_token_not_exist);
        }
        $now_ts = time();
        if (intval($user_token->is_ok) !== 1)
        {
            throw new AdvError(AdvError::user_token_deny);

        }
        if (intval($user_token->expires) < $now_ts)
        {
            throw new AdvError(AdvError::user_token_expired);
        }
        try
        {
            $this->user = User::model()->findByPk($user_token->user_id);
            $this->inputDataBox->add('user_id', $this->user->id);
        } catch (\Exception $e)
        {
            throw new AdvError(AdvError::user_token_error);
        }

    }


    public function checkSign()
    {

    }

    public function isDebug()
    {
        return Sys::app()->params['is_debug'];
    }
}
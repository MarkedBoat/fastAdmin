<?php

namespace modules\bee_invasion\v1\api\admin;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\admin\AdminTokenDao;
use modules\bee_invasion\v1\model\admin\Admin;
use modules\bee_invasion\v1\model\admin\rbac\RbacAction;
use modules\bee_invasion\v1\model\admin\rbac\RbacRole;


abstract class AdminBaseAction extends ActionBase
{
    public $dataSource = 'POST_ALL';

    /**
     * @var Admin
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
        $user_token = AdminTokenDao::model()->findOneByWhere(['user_token' => $token], false);
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
            $this->user = Admin::model()->findByPk($user_token->user_id);
            $this->inputDataBox->add('user_id', $this->user->id);
        } catch (\Exception $e)
        {
            throw new AdvError(AdvError::user_token_error);
        }
        $action = RbacAction::model()->getByUri($this->uri);

        $this->user->initRoles();
        if (!in_array(RbacRole::superAdmin, $this->user->role_codes, true))
        {
            if (count(array_intersect($this->user->role_codes, $action->getRoleCodes())) === 0)
            {
                throw new AdvError(AdvError::rbac_deny);
            }
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
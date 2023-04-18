<?php

namespace modules\_dp\v1\api;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\_dp\v1\dao\AdminTokenDao;
use modules\_dp\v1\model\Admin;
use modules\_dp\v1\model\rbac\RbacAction;
use modules\_dp\v1\model\rbac\RbacRole;


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

        session_start();
        if (isset($_SESSION['utk']))
        {
            $rsa_token = $_SESSION['utk'];
        }
        else
        {
            $rsa_token = $this->inputDataBox->tryGetString('user_token');
            if (!$rsa_token)
            {
                throw new AdvError(AdvError::user_token_not_exist);
            }
        }


        $pri_key    = file_get_contents(__ROOT_DIR__ . '/config/file/web/admin_bg.pri.key');
        $true_token = RSA::de($pri_key, $rsa_token);
        list($user_id, $expires) = explode('_', $true_token);
        $now_ts = time();
        if (intval($expires) < $now_ts)
        {
            throw new AdvError(AdvError::user_token_expired);
        }

        $user_token_dao = AdminTokenDao::model()->findOneByWhere(['user_id' => $user_id], false);
        if (empty($user_token_dao))
        {
            throw new AdvError(AdvError::user_token_not_exist);
        }
        if (intval($user_token_dao->is_ok) !== 1)
        {
            throw new AdvError(AdvError::user_token_deny);

        }

        try
        {
            $this->user = Admin::model()->findByPk($user_token_dao->user_id);
            $this->inputDataBox->add('user_id', $this->user->id);
        } catch (\Exception $e)
        {
            throw new AdvError(AdvError::user_token_error);
        }
        $action = RbacAction::model()->getByUri($this->uri);

        $this->user->initRoles();
        if (!in_array(RbacRole::superAdmin, $this->user->role_codes, true))
        {
            $action_codes = $action->getRoleCodes();
            if (count(array_intersect($this->user->role_codes, $action_codes)) === 0)
            {
                throw new AdvError(AdvError::rbac_deny, "您没有对应权限：user:{$this->user->id}  action:{$action->id} action_role_codes:[" . join(',', $action_codes) . ']');
            }
            Sys::app()->addLog(['user_codes' => $this->user->role_codes, 'action_codes' => $action_codes,], "action_access_ok intersect");
        }
        else
        {
            Sys::app()->addLog(['user_codes' => $this->user->role_codes], "action_access_ok super_admin");
        }

    }


    public function checkSign()
    {

    }

    public function isDebug()
    {
        return Sys::app()->params['is_debug'];
    }

    public function handleAdvError(AdvError $e)
    {

        if (substr($e->getDetailCode(),0,10)==='user_error' && $this->isOutputHtml())
        {

            //  $url=Sys::app()->params['url'];
            @header('content-Type:text/html;charset=utf8');
            header("refresh:3;url=/login");
            //var_dump($this->open_actions);
            echo $e->getMessage();
            return true;
            // return true;
        }
        else
        {
            return false;
        }
    }
}
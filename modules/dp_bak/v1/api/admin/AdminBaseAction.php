<?php

namespace modules\dp\v1\api\admin;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\dp\v1\dao\admin\AdminTokenDao;
use modules\dp\v1\model\admin\Admin;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\admin\rbac\RbacRole;


abstract class AdminBaseAction extends \modules\_dp\v1\api\AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    /**
     * @var Admin
     */
    protected $user;



    public function checkSign()
    {

    }

    public function isDebug()
    {
        return Sys::app()->params['is_debug'];
    }
}
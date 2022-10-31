<?php

namespace modules\dp\v1\api\admin\dev;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;


class ActionGit extends AdminBaseAction
{
    public $requestMethods = ['GET'];
    public $dataSource     = 'GEY';

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        $env    = $this->inputDataBox->getStringNotNull('env');
        $branch = $this->inputDataBox->tryGetString('branch');


        echo "\nSUCCESS\n";
        \models\Api::$hasOutput = true;
        //  return ['text' => $this->rawPostData];

    }

}
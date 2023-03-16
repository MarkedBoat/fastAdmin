<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionRender_tables extends ActionBase
{
    public $dataSource = 'GET';

    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeHtml);


        return $this->renderTpls(['/modules/_dp/v1/view/data/tables.html'], $this->inputDataBox->getData());

    }


}
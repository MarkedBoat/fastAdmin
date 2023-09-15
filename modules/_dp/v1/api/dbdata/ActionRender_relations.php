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


class ActionRender_relations extends ActionBase
{
    public $dataSource = 'GET';

    public function init()
    {
        $this->setOutputHtml();
        parent::init();
    }

    public function run()
    {
        return $this->renderTpls(['/modules/_dp/v1/view/data/relations.html'], $this->inputDataBox->getData());
    }


}
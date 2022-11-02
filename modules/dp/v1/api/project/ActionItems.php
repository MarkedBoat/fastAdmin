<?php

namespace modules\dp\v1\api\project;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\dao\project\StoryDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Project;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\Version;


class ActionItems extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $this->dispatcher->setOutType(Api::outTypeText);
        \models\Api::$hasOutput = true;
        @header('content-Type:application/json;charset=utf8');

        $item_code = $this->inputDataBox->getStringNotNull('item_code');
        $daos      = [];
        if ($item_code === 'project')
        {
            $daos = $this->getProjects();
        }
        else if ($item_code === 'version')
        {
            $daos = $this->getVersions();
        }
        else if ($item_code === 'story')
        {
            $daos = $this->getStorys();
        }
        else
        {
            throw new AdvError(AdvError::request_param_error, "{$item_code} 没有对应的处理方法");
        }
        $list = [['val' => 0, 'text' => '不选']];
        foreach ($daos as $dao)
        {
            $list[] = ['val' => $dao->id, 'text' => $dao->title];
        }

        die(json_encode($list));
    }

    public function getProjects()
    {
        return Project::model()->findAllByWhere(['is_ok' => Opt::YES]);
    }


    public function getVersions()
    {
        $project_id = $this->inputDataBox->getInt('project_id');

        return $project_id ? Version::model()->findAllByWhere(['project_id' => $project_id, 'is_ok' => Opt::YES]) : [];
    }

    public function getStorys()
    {
        $version_id = $this->inputDataBox->getInt('version_id');
        return $version_id ? Story::model()->findAllByWhere(['version_id' => $version_id, 'is_ok' => Opt::YES]) : [];
    }

}
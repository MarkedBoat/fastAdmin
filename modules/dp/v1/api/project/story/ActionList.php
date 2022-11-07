<?php

namespace modules\dp\v1\api\project\story;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\dao\project\ProjectStoryDao;
use modules\dp\v1\dao\project\StoryDao;
use modules\dp\v1\dao\project\VersionStoryDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Project;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;
use modules\dp\v1\model\project\Version;


class ActionList extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {


        $project_id = $this->inputDataBox->getStringNotNull('project_id');
        $version_id = $this->inputDataBox->getStringNotNull('version_id');


        $db         = 'dev_bg';
        $table_name = 'd_story';


        $attr       = $this->inputDataBox->tryGetArray('attr');
        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');

        $project_story_ids = $project_id === '#' ? false : array_map(function ($dao) { return $dao->story_id; }, ProjectStoryDao::model()->findAllByWhere(['is_ok' => Opt::YES, 'project_id' => intval($project_id)]));
        $version_story_ids = $version_id === '#' ? false : array_map(function ($dao) { return $dao->story_id; }, VersionStoryDao::model()->findAllByWhere(['is_ok' => Opt::YES, 'version_id' => intval($version_id)]));
        if ($project_story_ids !== false && $version_story_ids !== false)
        {
            $attr['id'] = array_intersect($project_story_ids, $version_story_ids);
        }
        else if ($project_story_ids === false && $version_story_ids !== false)
        {
            $attr['id'] = $version_story_ids;
        }
        else if ($version_story_ids === false && $project_story_ids !== false)
        {
            $attr['id'] = $project_story_ids;
        }
        if (isset($attr['id']) && count($attr['id']) === 0)
        {
            return ['rowsTotal' => 0, 'pageTotal' => 0, 'pageIndex' => $page_index, 'pageSize' => $page_size, 'dataRows' => [], 'msg' => 'project_id 和 version 交下来 空数据'];

        }

        $dbtable = new DbTable();
        $dbtable->setTable($db, $table_name)->setAttrs($attr)->setPage($page_index, $page_size);
        foreach ($sort_map as $sort_key => $sort_type)
        {
            $dbtable->addSort($sort_key, $sort_type);
        }
        Sys::app()->addLog($attr);
        return $dbtable->query();


    }


}
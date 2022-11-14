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
use modules\dp\v1\dao\project\ProjectVersionStoryDao;
use modules\dp\v1\dao\project\StoryDao;
use modules\dp\v1\dao\project\StoryVersionDao;
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


        $project_id        = $this->inputDataBox->getStringNotNull('project_id');
        $version_id        = $this->inputDataBox->getStringNotNull('version_id');
        $track_versoin_his = $this->inputDataBox->getStringNotNull('track_version_his') === 'yes';


        $db         = 'dev_bg';
        $table_name = 'd_story';


        $attr       = $this->inputDataBox->tryGetArray('attr');
        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');

        if ($track_versoin_his)
        {
            $version_story_ids = $version_id === '#' ? false : array_map(function ($dao) { return $dao->story_id; }, StoryVersionDao::model()->findAllByWhere(['is_ok' => Opt::YES, 'version_id' => intval($version_id)]));

            if ($version_story_ids !== false)
            {
                if (count($version_story_ids))
                {
                    $attr['id'] = $version_story_ids;
                }
                else
                {
                    return ['rowsTotal' => 0, 'pageTotal' => 0, 'pageIndex' => $page_index, 'pageSize' => $page_size, 'dataRows' => [], 'msg' => 'project_id 和 version 交下来 空数据'];
                }
            }
        }
        else
        {
            if ($version_id !== '#')
            {
                $attr['version_id'] = $version_id;
            }
        }


        if ($project_id !== '#')
        {
            $attr['project_id'] = $project_id;
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
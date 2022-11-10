<?php

namespace modules\dp\v1\api\project\story;

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
use modules\dp\v1\model\project\StoryCommit;
use modules\dp\v1\model\project\Version;


class ActionUpdate extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {

        $story_dao             = Story::model()->findByPk($this->inputDataBox->getStringNotNull('id'));
        $old_attrs             = $story_dao->getOuterDataArray();
        $story_dao->project_id = $this->inputDataBox->getInt('project_id');
        $story_dao->version_id = $this->inputDataBox->getInt('version_id');
        $story_dao->story_id   = $this->inputDataBox->getInt('story_id');
        $story_dao->title      = $this->inputDataBox->getStringNotNull('title');
        $story_dao->detail     = $this->inputDataBox->getStringNotNull('detail');;
        $story_dao->create_by = $this->user->id;
        $story_dao->update(true, true);
        $new_attrs = $story_dao->getOuterDataArray();

        $commit_dao             = new StoryCommit();
        $commit_dao->story_id   = $story_dao->id;
        $commit_dao->reply_id   = 0;
        $commit_dao->used_hours = 0;
        $commit_dao->step       = 'update_story';
        $commit_dao->detail     = json_encode(['new' => $new_attrs, 'old' => $old_attrs]);
        $commit_dao->create_by  = $this->user->id;
        $commit_dao->insert(true, true);

        return [
            'story' => $story_dao->getOuterDataArray(),
        ];
    }


}
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
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Project;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;


class ActionAddToProject extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $project_id    = $this->inputDataBox->getInt('project_id');
        $story_ids     = $this->inputDataBox->getArrayNotNull('story_ids');
        $story_changes = [];
        $project_dao   = Project::model()->findByPk($project_id);
        foreach ($story_ids as $story_id)
        {
            $story_changes[$story_id] = [
                'project' => false,
            ];
            $story_dao                = Story::model()->findByPk($story_id);

            $msgs              = [];
            $project_story_dao = ProjectStoryDao::model();
            $project_story_tn  = $project_story_dao->getTableName();
            if ($project_id === 0)
            {
                $sql        = "update {$project_story_tn} set is_ok=2 where story_id={$story_id}";
                $update_res = $project_story_dao->getDbConnect()->setText($sql)->execute();
                if ($update_res)
                {
                    $msgs[]                              = "添加至项目:[未跟踪] ";
                    $story_changes[$story_id]['project'] = true;
                }
            }
            else
            {
                $project_story_dao->project_id = $project_id;
                $project_story_dao->story_id   = $story_id;
                $project_res                   = $project_story_dao->setOnDuplicateKeyUpdate(['is_ok' => Opt::YES])->insert(false);
                if ($project_res)
                {
                    $msgs[]                              = "添加至项目:[{$project_dao->id}:$project_dao->title] ";
                    $story_changes[$story_id]['project'] = true;
                }

                $sql         = "update {$project_story_tn} set is_ok=2 where story_id={$story_id} and project_id=0";
                $update2_res = $project_story_dao->getDbConnect()->setText($sql)->execute();
                if ($update2_res)
                {
                    $msgs[] = "移出 项目:[未跟踪] ";
                }
            }


            if (count($msgs))
            {
                $commit_dao             = new StoryCommit();
                $commit_dao->story_id   = $story_dao->id;
                $commit_dao->reply_id   = 0;
                $commit_dao->used_hours = 0;
                $commit_dao->step       = 'comment';
                $commit_dao->detail     = join("  ", $msgs);
                $commit_dao->create_by  = $this->user->id;
                $commit_dao->insert(true, true);
            }
        }


        return [
            'changed' => $story_changes,
        ];

    }


}
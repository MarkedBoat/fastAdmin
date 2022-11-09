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
use modules\dp\v1\dao\project\StoryVersionDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Project;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;
use modules\dp\v1\model\project\Version;


class ActionMoveToProject extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $project_id = $this->inputDataBox->getInt('project_id');
        $story_ids  = $this->inputDataBox->getArrayNotNull('story_ids');

        $story_models = Story::model()->findAllByWhere(['id' => $story_ids]);

        if (count($story_models) === 0)
        {
            throw new AdvError(AdvError::request_param_verify_fail, '找点事实存在的story');

        }
        $check_project_id_msgs = [];
        /** @var  $story_model_map Story[] */
        $story_model_map = [];
        foreach ($story_models as $story_model)
        {
            if ($story_model->project_id !== 0 && $project_id !== 0)
            {
                $check_project_id_msgs[] = "[{$story_model->id}]:{$story_model->title} story 必须是属于【未追踪】 或者  转移到【未追踪】 [{$story_model->project_id}->{$project_id}]";
            }
            $story_model_map[$story_model->id] = $story_model;
        }
        if (count($check_project_id_msgs))
        {
            throw new AdvError(AdvError::request_param_verify_fail, join("\n", $check_project_id_msgs));
        }
        $project_dao = Project::model()->findByPk($project_id);

        $update_story_sqls = [];
        $story_tn          = $story_models[0]->getTableName();
        $record            = [];
        $commit_infos      = [];
        foreach ($story_ids as $story_id)
        {
            $story_id          = intval($story_id);
            $record[$story_id] = false;
            $story_model       = $story_model_map[$story_id];
            if ($story_model->project_id !== $project_id)
            {
                $update_story_sqls[] = "update {$story_tn} set project_id={$project_id} where id={$story_id}";
                $commit_infos[]      = ['story_id' => $story_id, 'type' => 'project_change', 'data' => ['old_project_id' => $story_model->project_id, 'new_project_id' => $project_id]];
                $record[$story_id]   = true;
            }

        }

        if (count($update_story_sqls))
        {
            $story_models[0]->getDbConnect()->setText(join(";\n", $update_story_sqls))->execute();
        }

        if (count($commit_infos))
        {
            $commit_dao  = new StoryCommit();
            $commit_sqls = [];
            $bind        = [];
            $commit_tn   = $commit_dao->getTableName();
            foreach ($commit_infos as $commit_info)
            {
                $commit_sqls[]                              = "insert ignore into {$commit_tn} set story_id={$commit_info['story_id']},reply_id=0,used_hours=0,step='{$commit_info['type']}',create_by={$this->user->id},detail=:detail_{$commit_info['story_id']}";
                $bind[":detail_{$commit_info['story_id']}"] = json_encode($commit_info['data']);
            }
            $commit_dao->getDbConnect()->setText(join(";\n", $commit_sqls))->bindArray($bind)->execute();
        }


        return [
            'changed' => $record,
        ];


    }


}
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


class ActionMoveOut extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $project_id        = $this->inputDataBox->getIntNotNull('project_id');
        $version_id        = $this->inputDataBox->getIntNotNull('version_id');
        $story_ids         = $this->inputDataBox->getArrayNotNull('story_ids');
        $story_changes     = [];
        $project_dao       = Project::model()->findByPk($project_id);
        $version_dao       = Version::model()->findByPk($version_id);
        $commit_dao        = new StoryCommit();
        $version_story_dao = VersionStoryDao::model();
        $project_story_dao = ProjectStoryDao::model();


        $commit_tn        = $commit_dao->getTableName();
        $version_story_tn = $version_story_dao->getTableName();
        $project_story_tn = $project_story_dao->getTableName();

        $insert_sqls              = [];
        $bind                     = [];
        $story_ids_str            = join(',', $story_ids);
        $exist_project_story_rows = $project_story_dao->getDbConnect()->setText("select id,story_id from {$project_story_tn} where project_id={$project_id} and story_id in ({$story_ids_str}) and is_ok=1 limit 1000")->queryAll();
        $exist_version_story_rows = $version_story_dao->getDbConnect()->setText("select id,story_id from {$version_story_tn} where version_id={$project_id} and story_id in ({$story_ids_str}) and is_ok=1 limit 1000")->queryAll();
        $exist_project_story_ids  = [];
        $exist_project_pks        = [];
        $exist_version_story_ids  = [];
        $exist_version_pks        = [];
        foreach ($exist_project_story_rows as $exist_project_story_row)
        {
            $exist_project_story_ids[] = intval($exist_project_story_row['story_id']);
            $exist_project_pks[]       = intval($exist_project_story_row['id']);
        }

        foreach ($exist_version_story_rows as $exist_version_story_row)
        {
            $exist_version_story_ids[] = intval($exist_version_story_row['story_id']);
            $exist_version_pks[]       = intval($exist_version_story_row['id']);
        }


        foreach ($story_ids as $story_id)
        {
            $story_id                 = intval($story_id);
            $story_changes[$story_id] = [
                'project' => false,
                'version' => false,
            ];
            $msgs                     = [];
            if (in_array($story_id, $exist_project_story_ids, true))
            {
                $story_changes[$story_id]['project'] = true;
                $msgs[]                              = "移出项目:[{$project_dao->id}:{$project_dao->title}] ";

            }
            if (in_array($story_id, $exist_version_story_ids, true))
            {
                $story_changes[$story_id]['version'] = true;
                $msgs[]                              = "移出目标版本:[{$version_dao->id}:$version_dao->title] ";
            }
            if (count($msgs))
            {
                $insert_sqls[]               = "insert ignore into {$commit_tn} set story_id={$story_id},reply_id=0,used_hours=0,step='comment',create_by={$this->user->id},detail=:detail_{$story_id}";
                $bind[":detail_{$story_id}"] = join("  ", $msgs);
            }

        }
        $story_changes['project_story_cnt'] = 0;
        if (count($exist_project_pks))
        {
            $tmp_ids_str                        = join(',', $exist_project_pks);
            $story_changes['project_story_cnt'] = $project_story_dao->getDbConnect()->setText("update {$project_story_tn} set is_ok=2 where id in ({$tmp_ids_str})")->execute();
        }
        $story_changes['version_story_cnt'] = 0;
        if (count($exist_version_pks))
        {
            $tmp_ids_str                        = join(',', $exist_version_pks);
            $story_changes['version_story_cnt'] = $version_story_dao->getDbConnect()->setText("update {$version_story_tn} set is_ok=2 where id in ({$tmp_ids_str})")->execute();
        }

        $story_changes['commit_cnt'] = 0;
        if (count($insert_sqls))
        {
            $story_changes['commit_cnt'] = $commit_dao->getDbConnect()->setText(join(";\n", $insert_sqls))->bindArray($bind)->execute();
        }

        return [
            'changed' => $story_changes,
        ];

    }


}
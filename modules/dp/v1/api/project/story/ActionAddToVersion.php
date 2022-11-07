<?php

namespace modules\dp\v1\api\project\story;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\dp\v1\api\admin\AdminBaseAction;
use modules\dp\v1\dao\admin\rbac\RbacRoleDao;
use modules\dp\v1\dao\project\VersionStoryDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;
use modules\dp\v1\model\project\Version;


class ActionAddToVersion extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $version_id    = $this->inputDataBox->getInt('version_id');
        $story_ids     = $this->inputDataBox->getArrayNotNull('story_ids');
        $story_changes = [];
        $version_dao   = Version::model()->findByPk($version_id);
        foreach ($story_ids as $story_id)
        {
            $story_changes[$story_id] = [
                'version' => false,
            ];
            $story_dao                = Story::model()->findByPk($story_id);
            $msgs                     = [];

            $version_story_dao = VersionStoryDao::model();
            $version_story_tn  = $version_story_dao->getTableName();

            if ($version_id === 0)
            {
                $sql        = "update {$version_story_tn} set is_ok=2 where story_id={$story_id}";
                $update_res = $version_story_dao->getDbConnect()->setText($sql)->execute();

                $version_story_dao->version_id = $version_id;
                $version_story_dao->story_id   = $story_id;
                $version_res                   = $version_story_dao->setOnDuplicateKeyUpdate(['is_ok' => Opt::YES])->insert(false);
                if ($version_res)
                {
                    $msgs[]                              = "添加至目标版本:[未跟踪] ";
                    $story_changes[$story_id]['version'] = true;
                }

            }
            else
            {
                $version_story_dao->version_id = $version_id;
                $version_story_dao->story_id   = $story_id;
                $version_res                   = $version_story_dao->setOnDuplicateKeyUpdate(['is_ok' => Opt::YES])->insert(false);
                if ($version_res)
                {
                    $msgs[]                              = "添加至目标版本:[{$version_dao->id}:$version_dao->title] ";
                    $story_changes[$story_id]['version'] = true;
                }

                $sql         = "update {$version_story_tn} set is_ok=2 where story_id={$story_id} and version_id=0";
                $update2_res = $version_story_dao->getDbConnect()->setText($sql)->execute();
                if ($update2_res)
                {
                    $msgs[] = "移出 目标版本:[未跟踪] ";
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
<?php

namespace modules\dp\v1\api\project\story\commit;

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
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;
use function MongoDB\BSON\toJSON;


class ActionAdd extends AdminBaseAction
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'POST';


    public function run()
    {
        $commit_dao             = new StoryCommit();
        $commit_dao->story_id   = $this->inputDataBox->getIntNotNull('story_id');
        $commit_dao->reply_id   = $this->inputDataBox->getInt('reply_id');
        $commit_dao->used_hours = $this->inputDataBox->getInt('used_hours');
        $commit_dao->step       = $this->inputDataBox->getStringNotNull('step');
        $commit_dao->detail     = $this->inputDataBox->getString('detail');
        $commit_dao->create_by  = $this->user->id;

        $story_dao = Story::model()->findByPk($commit_dao->story_id);

        if (in_array($commit_dao->step, ['create_story', 'update_story']))
        {
            throw new AdvError(AdvError::request_param_error, '此类状态不允许提交');
        }

        if ($commit_dao->step === 'confirm')
        {
            if (!in_array($story_dao->step, ['create_story', 'confirm'], true))
            {
                throw new AdvError(AdvError::request_param_error, '一经确认，不能再修改');
            }
        }

        if ($commit_dao->step === 'end')
        {
            $child_stories = Story::model()->findAllByWhere(['is_ok' => Opt::YES, 'story_id' => $story_dao->id]);
            if (count($child_stories))
            {
                $child_steps = array_unique(array_map(function ($story) { return $story->step; }, $child_stories));
                $diff        = array_diff($child_steps, ['end', 'close', 'del']);
                if (count($diff))
                {
                    throw new AdvError(AdvError::data_info_unexpected, '必须【结束/完成】掉所有子问题才行', [$child_steps, $diff, $child_stories]);
                }
            }
        }

        if ($commit_dao->step === 'close')
        {
            $child_stories = Story::model()->findAllByWhere(['is_ok' => Opt::YES, 'story_id' => $story_dao->id]);
            if (count($child_stories))
            {
                $child_steps = array_unique(array_map(function ($story) { return $story->step; }, $child_stories));
                $diff        = array_diff($child_steps, ['end', 'close', 'del']);
                if (count($diff) !== 1)
                {
                    throw new AdvError(AdvError::data_info_unexpected, '必须【关闭】掉所有子问题才行', [$child_steps, $diff, $child_stories]);
                }
            }
        }

        if ($commit_dao->step === 'del')
        {
            if (empty($commit_dao->detail))
            {
                throw  new AdvError(AdvError::request_param_verify_fail, '删除story 必须要填写理由');
            }
            $child_stories = Story::model()->findAllByWhere(['is_ok' => Opt::YES, 'story_id' => $story_dao->id]);
            if (count($child_stories))
            {
                $child_steps = array_unique(array_map(function ($story) { return $story->step; }, $child_stories));
                $diff        = array_diff($child_steps, ['end', 'close', 'del']);
                if (count($diff))
                {
                    throw new AdvError(AdvError::data_info_unexpected, '必须【关闭】掉所有子问题才行', [$child_steps, $diff, $child_stories]);
                }
            }
        }


        $commit_dao->insert(true, true);

        if ($commit_dao->step !== 'comment')
        {
            if ($story_dao->step !== $commit_dao->step)
            {
                $story_dao->step = $commit_dao->step;
                $story_dao->update(true, true);
            }
        }

        return [
            'commit' => $commit_dao->getOuterDataArray()
        ];
    }


}
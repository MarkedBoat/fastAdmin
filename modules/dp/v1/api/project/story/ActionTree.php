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


class ActionTree extends AdminBaseAction
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'POST';

    private $storys           = [];
    private $parent_story_ids = [];

    public function run()
    {
        $child_id  = $this->inputDataBox->getIntNotNull('child_story_id');
        $parent_id = $this->inputDataBox->getInt('parent_story_id');

        $child_story = StoryDao::model()->findByPk($child_id);
        if ($parent_id)
        {
            $this->parent_story_ids[] = $child_id;

            $res = $this->getParentStoryEnd($parent_id);
            if ($res === false)
            {
                throw new AdvError(AdvError::data_info_unexpected, '请查看是否循环绑定了');
            }
            $child_story->story_id = $parent_id;
            // $child_story->story_lev = $parent_story->story_lev + 1;
        }
        else
        {
            $child_story->story_id = 0;
            //  $child_story->story_lev = 1;
        }
        $child_story->update();
        Sys::app()->addLog($this->storys, 'storys');
        return $child_story->getOuterDataArray();
    }

    public function getParentStoryEnd($story_id)
    {
        if (in_array($story_id, $this->parent_story_ids, true))
        {
            return false;
        }
        $story          = StoryDao::model()->findByPk($story_id);
        $this->storys[] = $story;
        if ($story->story_id === 0)
        {
            return true;
        }
        return $this->getParentStoryEnd($story->story_id);
    }

}
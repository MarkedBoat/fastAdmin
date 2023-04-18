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


class ActionSort extends AdminBaseAction
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'POST';



    public function run()
    {
        $sorts      = $this->inputDataBox->getArrayNotNull('sorts');
        $version_id = $this->inputDataBox->getIntNotNull('version_id');
        $sort_map   = [];
        foreach ($sorts as $sort)
        {
            $sort_map[intval($sort['id'])] = intval($sort['sn']);
        }
        $story_daos = StoryDao::model()->findAllByWhere(['version_id' => $version_id]);
        foreach ($story_daos as $story_dao)
        {
            if (isset($sort_map[$story_dao->id]))
            {
                if ($sort_map[$story_dao->id] === $story_dao->story_desc_order)
                {
                    $sort_map[$story_dao->id] = 'no change';
                }
                else
                {
                    $story_dao->story_desc_order = $sort_map[$story_dao->id];
                    $sort_map[$story_dao->id]    = $story_dao->update();
                }
            }
        }
        return $sort_map;
    }


}
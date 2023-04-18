<?php

namespace modules\dp\v1\api\project\version;

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
        $isSkipTimeOver = $this->inputDataBox->tryGetString('isSkipTimeOver') === 'yes';
        $dao            = Version::model();
        $tn             = $dao->getTableName();
        $field_map      = $dao->getFieldMap();
        $str            = join(',', array_map(function ($key) { return "`{$key}`"; }, array_keys($field_map)));
        if ($isSkipTimeOver)
        {
            return ['list' => $dao->getDbConnect()->setText("select {$str} from {$tn} where is_ok=1 and end_date>now();")->queryAll()];
        }
        else
        {
            return ['list' => $dao->getDbConnect()->setText("select {$str} from {$tn} where is_ok=1 ;")->queryAll()];
        }

    }


}
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
use modules\dp\v1\dao\project\VersionStoryDao;
use modules\dp\v1\model\admin\dbdata\DbColumn;
use modules\dp\v1\model\admin\dbdata\DbTable;
use modules\dp\v1\model\admin\rbac\RbacAction;
use modules\dp\v1\model\project\Project;
use modules\dp\v1\model\project\Story;
use modules\dp\v1\model\project\StoryCommit;
use modules\dp\v1\model\project\Version;


class ActionMoveOutVersion extends AdminBaseAction
{
    public $requestMethods = ['POST', 'GET'];
    public $dataSource     = 'REQUEST';


    public function run()
    {
        $version_id = $this->inputDataBox->getInt('version_id');
        $story_ids  = $this->inputDataBox->getArrayNotNull('story_ids');


        $story_models = Story::model()->findAllByWhere(['id' => $story_ids]);

        if (count($story_models) === 0)
        {
            throw new AdvError(AdvError::request_param_verify_fail, '找点事实存在的story');

        }
        /** @var  $story_model_map Story[] */
        $story_model_map = [];
        foreach ($story_models as $story_model)
        {
            $story_model_map[$story_model->id] = $story_model;
        }
        $version_dao = Version::model()->findByPk($version_id);

        $sv_model  = StoryVersionDao::model();
        $sv_tn     = $sv_model->getTableName();
        $sv_models = $sv_model->findAllByWhere(['story_id' => $story_ids]);

        /** @var  $sv_models_map_map StoryVersionDao[][] */
        $sv_models_map_map = [];
        foreach ($sv_models as $sv_model_1)
        {
            if (!isset($sv_models_map_map[$sv_model_1->story_id]))
            {
                $sv_models_map_map[$sv_model_1->story_id] = [];
            }
            $sv_models_map_map[$sv_model_1->story_id][$sv_model_1->version_id] = $sv_model_1;
        }
        Sys::app()->addLog([$sv_models_map_map], 'xxxxxx');


        $update_sv_ok_ids   = [];
        $update_sv_deny_ids = [];
        $insert_sv_sqls     = [];


        $record       = [];
        $commit_infos = [];
        foreach ($story_ids as $story_id)
        {
            $story_id          = intval($story_id);
            $record[$story_id] = [
                'version'   => false,
                'unversion' => false,
            ];
            $story_model       = $story_model_map[$story_id];

            if (isset($sv_models_map_map[$story_id]))
            {
                $sv_version_map = $sv_models_map_map[$story_id];

                if (isset($sv_version_map[$version_id]))
                {
                    //移出当前目标版本
                    if ($sv_version_map[$version_id]->is_ok === Opt::YES)
                    {

                        $update_sv_deny_ids[]         = $sv_version_map[$version_id]->id;
                        $record[$story_id]['version'] = 'shutdown';
                        $commit_infos[]               = ['story_id' => $story_id, 'type' => 'version_change', 'data' => ['type' => 'out', 'version_id' => $version_id]];
                    }
                    else
                    {
                        $record[$story_id]['version'] = 'keep_shutdown';
                    }
                }
                else
                {
                    $record[$story_id]['version'] = 'not_found_version';
                }
                //瞅一下  除了【未跟踪】之外，还有没有活着的
                $has_open = false;
                foreach ($sv_version_map as $fetch_version_id => $curr_sv_model)
                {
                    if ($fetch_version_id === $version_id)
                    {
                        continue;
                    }
                    if ($fetch_version_id === 0)
                    {
                        continue;
                    }
                    if ($curr_sv_model->is_ok === Opt::YES)
                    {
                        $has_open = true;
                        break;
                    }

                }

                if ($has_open === false)
                {
                    //如果没有或者的目标版本，那么  【未跟踪】 就要打开
                    if (isset($sv_version_map[0]))
                    {
                        if ($sv_version_map[0]->is_ok === Opt::YES)
                        {
                            $record[$story_id]['unversion'] = 'keep_open';
                        }
                        else
                        {
                            $update_sv_ok_ids[]           = $sv_version_map[0]->id;
                            $record[$story_id]['version'] = 'open';
                            $commit_infos[]               = ['story_id' => $story_id, 'type' => 'version_change', 'data' => ['type' => 'in', 'version_id' => 0]];
                        }
                    }
                    else
                    {
                        //最简单，直接插入
                        $insert_sv_sqls[]               = "insert ignore into {$sv_tn} set story_id={$story_id},version_id=0,is_ok=1";
                        $record[$story_id]['unversion'] = 'open';
                        $commit_infos[]                 = ['story_id' => $story_id, 'type' => 'version_change', 'data' => ['type' => 'in', 'version_id' => 0]];
                    }
                }
                else
                {
                    //还有活着的目标版本，那么  【未跟踪】 就要关闭
                    if (isset($sv_version_map[0]))
                    {
                        if ($sv_version_map[0]->is_ok === Opt::YES)
                        {
                            $update_sv_deny_ids[]         = $sv_version_map[0]->id;
                            $record[$story_id]['version'] = 'shutdown';
                            $commit_infos[]               = ['story_id' => $story_id, 'type' => 'version_change', 'data' => ['type' => 'out', 'version_id' => 0]];
                        }
                        else
                        {
                            $record[$story_id]['unversion'] = 'keep_shutdown';
                        }
                    }
                    else
                    {
                        $record[$story_id]['unversion'] = 'keep_shutdown_not_found';
                    }
                }

            }
            else
            {
                $record[$story_id]['version'] = 'not_found_version_and_story';
            }

        }

        if (count($update_sv_ok_ids))
        {
            $tmp_str = join(',', $update_sv_ok_ids);
            $sv_model->getDbConnect()->setText("update {$sv_tn} set is_ok=1 where id in ({$tmp_str})")->execute();
        }

        if (count($update_sv_deny_ids))
        {
            Sys::app()->addLog($update_sv_deny_ids, '$update_sv_deny_ids');
            $tmp_str = join(',', $update_sv_deny_ids);
            $sv_model->getDbConnect()->setText("update {$sv_tn} set is_ok=2 where id in ({$tmp_str})")->execute();
        }

        if (count($insert_sv_sqls))
        {
            $sv_model->getDbConnect()->setText(join(";\n", $insert_sv_sqls))->execute();
        }

        if (count($commit_infos))
        {
            $commit_dao = new StoryCommit();

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
            'changed'        => $record,
            'update_ok_sv'   => $update_sv_deny_ids,
            'update_deny_sv' => $update_sv_deny_ids,
            'insert_sv'      => $insert_sv_sqls,
            'commit_infos'   => $commit_infos,


        ];

    }

}
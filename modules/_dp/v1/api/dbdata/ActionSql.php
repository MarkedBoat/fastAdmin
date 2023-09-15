<?php

namespace modules\_dp\v1\api\dbdata;

use Cassandra\Column;
use models\Api;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\api\AdminBaseAction;
use modules\_dp\v1\dao\rbac\RbacRoleDao;
use modules\_dp\v1\model\dbdata\DbColumn;
use modules\_dp\v1\model\dbdata\DbDbConf;
use modules\_dp\v1\model\dbdata\DbTable;
use modules\_dp\v1\model\rbac\RbacAction;


class ActionSql extends AdminBaseAction
{
    public $dataSource = 'POST_ALL';

    public function run()
    {

        // $sql       = $this->inputDataBox->getStringNotNull('sql');
        // $db_code = $this->inputDataBox->getStringNotNull('dbconf_name');

        $page_index = $this->inputDataBox->tryGetInt('page_index');
        $page_size  = $this->inputDataBox->tryGetInt('page_size');
        $sort_map   = $this->inputDataBox->tryGetArray('sort');


        $db_code = 'markedboat';


        $count_sql = '';
        // $sql       = 'select `ac`.`xc_1` as b_1,bc.yc as a_1 from a as ac left join b as bc on a.x=b.y where a.x=1 and b.x=1';
        $sql = "select r.id as rid,r.is_ok,r.tag_id,r.content_id,t.name,m.subject from kz_mark_tag as r left join kz_tag as t on r.tag_id=t.id left join kz_mark as m on m.id=r.content_id and r.content_type='mark' where m.id=1798 order by id desc limit 1;";

        //移除 order 和limit 方便之后拼接
        $sql = preg_replace('/(\s+order\s+by .*+;?)$/imSU', '', $sql);
        $sql = preg_replace('/(\s+limit\s+.*+;?)$/imSU', '', $sql);

        // $sql = 'select `ac`.`xc_1`,bc.yc from axx as `ac` left join `bxx` as bc on a.x=b.y  where a.x=1 and b.x=1';


        preg_match('/from(.*)?where/imSU', $sql, $from_str_macthed);
        //  var_dump($from_str_macthed);
        preg_match('/([\w`]+\s)/imS', $from_str_macthed[1], $from1st_matched);
        // var_dump($from1st_matched);
        $tablename1st = trim($from1st_matched[0]);

        preg_match_all('/([\w`]+\s+as\s+[\w`]+\s)/imSU', $from_str_macthed[1], $form_as_matched);
        //  var_dump($form_as_matched);
        $tablename_alias      = [];   //找出有别名的表，在鉴权中使用
        $tablename1st_as_name = false;//最左边的表，方便统计 生成count_sql
        if (isset($form_as_matched[0]))
        {
            foreach ($form_as_matched[0] as $form_as_str)
            {
                $tmp_ar        = explode('as', trim($form_as_str));
                $raw_tablename = trim($tmp_ar[0], " \t\n\r\0\x0B`");
                $as_tablename  = trim($tmp_ar[1], " \t\n\r\0\x0B`");
                //var_dump([$raw_tablename, $as_tablename]);
                $tablename_alias[$as_tablename] = $raw_tablename;
                if ($raw_tablename === $tablename1st)
                {
                    $tablename1st_as_name = $as_tablename;
                }
            }
        }
        else
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::format_param_error['detail'], "sql from 之后匹配不到表名", false);
        }


        // $sql = 'select ac.xc_1,bc.yc as a from a left join b on a.x=b.y where a.x=1 and b.x=1';
        // /select\s+(([\s\w.`]+(\s+as\s+\w+\s?)?,?)+)\s+from/  在js Stribg.match 中能用，但是比较慢，在phpstorm 也能用，但是在php中必须加上U贪婪修饰符
        // preg_match('/select\s+(([\s\w.`]+(\s+as\s+\w+\s?)?,?)+)\s+from/imSU', $sql, $ar3);
        $select_parts_pattern = '/select\s+((\s?[\w`]+\.[\w`]+(\s+as\s+\w+\s?)?,?)+)\s+from/imSU';
        preg_match($select_parts_pattern, $sql, $tmp_ar);
        if (!isset($tmp_ar[1]))
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::format_param_error['detail'], "sql中选择字段部分必须是 [表名.字段]", false);
        }

        //找出选中的字段，为了鉴权
        $select_parts = explode(',', str_replace('`', '', $tmp_ar[1]));
        //    var_dump($tmp_ar, $select_parts);
        $table_columns = [];
        foreach ($select_parts as $select_part)
        {
            $select_part          = trim($select_part);
            $table_column_matched = [];
            preg_match('/^([\w`]+)\.([\w`]+)/', $select_part, $table_column_matched);
            // var_dump($select_part, $table_column_matched);
            if (isset($table_column_matched[1]) && isset($table_column_matched[2]))
            {
                $raw_tablename = isset($tablename_alias[$table_column_matched[1]]) ? $tablename_alias[$table_column_matched[1]] : $table_column_matched[1];
                if (!isset($table_columns[$raw_tablename]))
                {
                    $table_columns[$raw_tablename] = [];
                }
                $table_columns[$raw_tablename][] = $table_column_matched[2];
            }
        }
        //   var_dump($sql, $table_columns);


        //  $this->dispatcher->setOutType(Api::outTypeText);
        //  \models\Api::$hasOutput = true;


        //排序数据
        $sort_data   = [];
        $sort_errors = [];
        foreach ($sort_map as $sort_key => $sort_type)
        {
            $r = preg_match('/^([\w`]+)\.([\w`]+)$/imSU', $sort_key);
            if (empty($r))
            {
                $sort_errors[] = $sort_key;
            }
            else
            {
                $sort_data[] = "`{$sort_key}` {$sort_type}";
            }

        }
        if (count($sort_errors))
        {
            $tmp_str = join(',', $sort_errors);
            return $this->dispatcher->createInterruptionInfo(AdvError::format_param_error['detail'], "sort key格式匹配不上 {$tmp_str}", false);
        }

        //分页数据
        $page_index   = $page_index < 1 ? 1 : $page_index;
        $page_size    = $page_size < 1 ? 20 : $page_size;
        $limit_offset = ($page_index - 1) * $page_size;


        $is_super      = in_array('_super_admin', $this->user->role_codes, true);
        $db_conf_model = DbDbConf::model()->findOneByWhere(['db_code' => $db_code, 'is_ok' => Opt::YES]);
        if ($is_super === false && $db_conf_model->checkAccess($this->user) === false)
        {
            return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "无权访问Db:[{$db_code}]", false);
        }
        if ($is_super === false)
        {
            $errors = [];
            foreach ($table_columns as $tablename => $columns)
            {
                $true_tablename = DbTable::replaceFakeTableName($tablename);

                $table_conf_model = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $true_tablename, 'is_ok' => Opt::YES]);

                if ($tablename === $tablename1st)
                {
                    $count_sql = preg_replace($select_parts_pattern, "select count({$tablename1st_as_name}.{$table_conf_model->pk_key}) from", $sql);
                    //var_dump($count_sql);
                }
                $table_conf_model->pk_key;
                if ($table_conf_model->checkAccess($this->user) === false)
                {
                    $errors[] = "{$db_code}.{$tablename}";
                }
                else
                {
                    /*
                    $column_models = $table_conf_model->getBizTableColumns();
                    $error_columns=[];
                    foreach ($column_models as $column_model){
                        if ($column_model->checkSelectAccess($this->user) === false)
                        {
                            $errors[] = $column_model->column_name;
                        }
                    }
                    */
                }
            }
            if (count($errors))
            {
                $tmp_str = join(',', $errors);
                return $this->dispatcher->createInterruptionInfo(AdvError::rbac_deny['detail'], "无权访问表:[{$tmp_str}]", false);
            }
        }
        else
        {
            $true_tablename = DbTable::replaceFakeTableName($tablename1st);

            $table_conf_model = DbTable::model()->findOneByWhere(['dbconf_name' => $db_code, 'table_name' => $true_tablename, 'is_ok' => Opt::YES]);
            $count_sql        = preg_replace($select_parts_pattern, "select count({$tablename1st_as_name}.{$table_conf_model->pk_key}) from", $sql);
            // var_dump($count_sql);
        }

        //  $db_conf_model->getConfDbConnect()->setText($sql)->queryRow();

        // return $res;

        $rows_count = intval($db_conf_model->getConfDbConnect()->setText($count_sql)->queryScalar());

        $str_limit = " limit {$limit_offset},{$page_size}";


        $str_sort  = count($sort_data) ? ("order by " . join(',', $sort_data)) : '';
        $query_sql = "{$sql} {$str_sort} {$str_limit}";
    //    var_dump($query_sql, $count_sql);

        $page_total = ceil($rows_count / $page_size);


        return ['rowsTotal' => $rows_count, 'pageTotal' => $page_total, 'pageIndex' => $page_index, 'pageSize' => $page_size, 'sql' => $query_sql, 'dataRows' => $db_conf_model->getConfDbConnect()->setText($query_sql)->queryAll()];


    }


}
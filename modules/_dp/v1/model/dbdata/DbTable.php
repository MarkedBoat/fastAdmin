<?php

namespace modules\_dp\v1\model\dbdata;


use models\common\opt\Opt;
use models\common\param\DataBox;
use models\common\sys\Sys;
use models\ext\tool\filter\SqlFilter;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbTableDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;
use modules\_dp\v1\dao\game\RoleDao;
use modules\_dp\v1\dao\game\RoleLevCfgDao;
use modules\_dp\v1\dao\user\UserCgHisDao;
use modules\_dp\v1\dao\user\UserDao;
use modules\_dp\v1\dao\user\UserInviterDao;
use modules\_dp\v1\model\Admin;
use modules\_dp\v1\model\TCache;
use modules\_dp\v1\model\TInfo;

class DbTable extends DbTableDao
{


    private $debug;


    private $_table_name      = '';
    private $_dbconf_name     = '';
    private $main_table_model;
    private $column_model_map = [];
    private $column_names     = [];

    private $attrs = [];
    /**
     * @var DbRelation[]
     */
    private $input_related_models = [];


    private $sort_key  = '';
    private $sort_type = '';
    private $sort_map  = [];

    private $limit_offset = 0;
    private $limit_size   = 20;
    private $page_index   = 0;

    private $ext_sql_opts = [];

    public static $db_connects = [];

    /**
     * @var static
     */
    private $table_model;
    /**
     * @var DbColumn[]
     */
    private $column_models = [];
    /**
     * @var DbRelation[]
     */
    private $relation_models = [];


    /**
     * @param $dbconf_name
     * @param $table_name
     * @return static|array
     * @throws \models\common\error\AdvError
     */
    public function setTable($dbconf_name, $table_name)
    {
        $this->dbconf_name = $dbconf_name;
        $this->table_name  = $table_name;

        if (!isset($this->main_table_model))
        {
            $this->main_table_model = $this->findOneByWhere(['table_name' => $this->table_name, 'dbconf_name' => $this->dbconf_name, 'is_ok' => 1], false);
        }

        if (empty($this->main_table_model))
        {
            throw new \Exception(" 表不存在 {$this->dbconf_name}.{$this->table_name}");
        }
        $column_tn     = DbColumn::$tableName;
        $column_models = DbColumn::model()->findAllByWhere([
            'table_name'  => $this->table_name,
            'dbconf_name' => $this->dbconf_name,
            'is_ok'       => Opt::isOk
        ]);

        if (empty($column_models))
        {
            throw new \Exception("查不到{$this->dbconf_name}.{$this->table_name}对应字段配置");
        }
        $this->column_names = [];
        foreach ($column_models as $column_model)
        {
            $this->column_model_map[$column_model->column_name] = $column_model;
            $this->column_names[]                               = "{$column_model->table_name}.`{$column_model->column_name}`";
        }

        return $this;
    }

    public function setAttrs($attrs)
    {
        $exist_attrs = [];
        $this->attrs = [];
        foreach ($this->column_models as $column_model)
        {
            if ($column_model->is_ok && isset($attrs[$column_model->column_name]))
            {
                $this->attrs[$column_model->column_name] = $attrs[$column_model->column_name];
            }
        }
        $this->input_related_models = [];
        foreach ($this->relation_models as $relation_model)
        {
            if ($relation_model->is_ok && isset($attrs["__relat__{$relation_model->relation_res_key}"]) && count($attrs["__relat__{$relation_model->relation_res_key}"]) > 0)
            {
                $this->input_related_models[$relation_model->relation_res_key] = $relation_model->setInputVals($attrs["__relat__{$relation_model->relation_res_key}"]);

            }
        }

        return $this;
    }

    public function addSort($sort_key, $sort_type)
    {
        if (in_array($sort_type, ['desc', 'asc'], true))
        {
            $this->sort_map[$sort_key] = $sort_type;
        }
        return $this;
    }

    public function setPage($page_index, $page_size)
    {
        $page_index < 1 ? ($page_index = 1) : null;
        $page_size < 1 ? ($page_size = 20) : null;
        $this->limit_offset = ($page_index - 1) * $page_size;
        $this->limit_size   = $page_size;
        $this->page_index   = $page_index;
        return $this;
    }

    /**
     * @param array $ext_sql_opt 连表   [select=>'',from=>'',where=>'']
     * @return $this
     */
    public function addExtSqlOpt($ext_sql_opt)
    {
        if (!isset($ext_sql_opt['select']))
        {
            $ext_sql_opt['select'] = false;
        }
        if (!isset($ext_sql_opt['from']))
        {
            $ext_sql_opt['from'] = false;
        }
        if (!isset($ext_sql_opt['where']))
        {
            $ext_sql_opt['where'] = false;
        }

        $this->ext_sql_opts[] = $ext_sql_opt;
        return $this;
    }

    public function getDbconfConnect()
    {
        if (!isset(self::$db_connects[$this->dbconf_name]))
        {
            self::$db_connects[$this->dbconf_name] = DbDbConf::model()->findOneByWhere(['is_ok' => Opt::YES, 'db_code' => $this->dbconf_name])->getConfDbConnect();
        }
        return self::$db_connects[$this->dbconf_name];
    }

    /**
     *
     * @return array [dataRows,count,sql,bind]
     * @throws \Exception
     */
    public function query()
    {
        $db             = $this->getDbconfConnect();
        $where          = '';
        $bind           = [];
        $left_join_strs = [];
        $ext_froms      = [];
        $ext_wheres     = [];
        if (count($this->attrs) > 0)
        {
            $tmp_ar = [];
            foreach ($this->attrs as $key => $val)
            {
                if (is_string($val) || is_int($val))
                {
                    if (strlen($val) === 0)
                    {
                        continue;
                    }


                    if (is_string($val) && substr($val, 0, 5) === 'like:')
                    {
                        $tmp_ar[] = " {$this->table_name}.`{$key}` like :{$key} ";
                        //$val             = substr($val, 5);
                        $bind[":{$key}"] = substr($val, 5);
                    }
                    else
                    {
                        $tmp_ar[]        = " {$this->table_name}.`{$key}`=:{$key} ";
                        $bind[":{$key}"] = $val;
                    }
                }
                else if (is_array($val))
                {
                    $tmp_ar2 = [];
                    foreach ($val as $i => $sub_val)
                    {
                        $tmp_ar2[]            = ":{$key}_{$i}";
                        $bind[":{$key}_{$i}"] = $sub_val;//后期补上 针对类型的
                    }
                    $tmp_str  = join(',', $tmp_ar2);
                    $tmp_ar[] = " {$this->table_name}.`{$key}` in ($tmp_str)";
                }
                else
                {
                    throw new \Exception('混进来了什么奇怪的东西' . $key);
                }
            }
            count($tmp_ar) ? $where = " where " . join(' and ', $tmp_ar) : null;
        }

        if (count($this->input_related_models) > 0)
        {
            $tmp_ar = [];
            foreach ($this->input_related_models as $relation)
            {
                $sql_join_parts = [];
                if (strlen($relation->relation_ext_field))
                {
                    $sql_join_parts[] = " and {$relation->relation_table_name}.`{$relation->relation_ext_field}`='{$relation->relation_ext_field_val}' ";// 没错，这种扩展值，只接受string
                }
                $sql_left_join_str = join(' ', $sql_join_parts);
                $left_join_strs[]  = " left join {$relation->relation_table_name} on {$relation->relation_table_name}.{$relation->relation_left_field}={$relation->left_table_name}.{$relation->left_table_index_field} {$sql_left_join_str} ";
                $vals              = $relation->getInputVals();
                $tmp_ar2           = [];
                foreach ($vals as $val_i => $val)
                {
                    $tmp_ar2[]                                       = ":{$relation->relation_res_key}_{$val_i}";
                    $bind[":{$relation->relation_res_key}_{$val_i}"] = $val;//后期补上 针对类型的
                }
                $tmp_str      = join(',', $tmp_ar2);
                $ext_wheres[] = " {$relation->relation_table_name}.`{$relation->relation_right_field}` in ($tmp_str)";

            }

        }

        if ($this->page_index)
        {
            $str_limit = " limit {$this->limit_offset},{$this->limit_size}";
        }
        else
        {
            $str_limit = ' limit 20';
        }
        $str_sort = '';
        if (count($this->sort_map))
        {
            $tmp_ar = [];
            foreach ($this->sort_map as $sort_key => $sort_type)
            {
                $tmp_ar[] = "`{$sort_key}` {$sort_type}";
            }
            $str_sort = "order by " . join(',', $tmp_ar);
        }
        $column_names_str = join(",", $this->column_names);

        foreach ($this->ext_sql_opts as $ext_sql_opt)
        {
            if (is_array($ext_sql_opt))
            {
                if ($ext_sql_opt['select'])
                {
                    $column_names_str = "{$column_names_str} ,{$ext_sql_opt['select']}";
                }
                if ($ext_sql_opt['from'])
                {
                    $ext_froms[] = $ext_sql_opt['from'];
                }
                if ($ext_sql_opt['where'])
                {
                    $ext_wheres[] = $ext_sql_opt['where'];

                }
            }
        }
        $left_join_str = join(' ', $left_join_strs);
        $ext_from      = join(' ', $ext_froms);
        //$where    = $where . count($ext_wheres) > 0 ? (($where ? (" and ") : (" where ")) . join(' and ', $ext_wheres)) : '';
        if (count($ext_wheres))
        {
            $where = $where . ($where ? (" and ") : (" where ")) . join(' and ', $ext_wheres);
        }
        $sql     = "select {$column_names_str} from {$this->table_name} {$left_join_str} {$ext_from} {$where} {$str_sort} {$str_limit}";
        $sql_cnt = "select count({$this->table_name}.`{$this->main_table_model->pk_key}`) from {$this->table_name}  {$left_join_str} {$ext_from} {$where} ";
        //  var_dump(['sqls' => [$sql, $sql_cnt], 'bind' => $bind]);
        // die;
        Sys::app()->addLog(['sqls' => [$sql, $sql_cnt], 'bind' => $bind], 'dbtable->query');
        $list  = $db->setText($sql)->bindArray($bind)->queryAll();
        $count = $db->setText($sql_cnt)->bindArray($bind)->queryScalar();

        if (count($list))
        {
            $list = $this->appendExplainInfo($list);
            $list = $this->appendRelatData($list);
        }

        $page_total = ceil($count / $this->limit_size);
        if ($this->debug)
        {
            return ['rowsTotal' => intval($count), 'pageTotal' => $page_total, 'pageIndex' => $this->page_index, 'pageSize' => $this->limit_size, 'sql' => $sql, 'bind' => $bind, 'dataRows' => $list];
        }
        else
        {
            return ['rowsTotal' => intval($count), 'pageTotal' => $page_total, 'pageIndex' => $this->page_index, 'pageSize' => $this->limit_size, 'dataRows' => $list];
        }
    }

    public function appendExplainInfo($query_all_list)
    {
        $column_explain_tn = DbColumnExplain::$tableName;
        $rows              = $this->getDbConnect()->setText("select * from {$column_explain_tn} where index_dbconf_name='{$this->dbconf_name}' and index_table_name='{$this->table_name}' and is_ok=1;")->queryAll();

        $filter = new SqlFilter();
        foreach ($rows as $relat_info_row)
        {
            $src_vals             = [];
            $src_db               = $relat_info_row['explain_dbconf_name'];
            $src_table            = $relat_info_row['explain_table_name'];
            $src_val_column       = $relat_info_row['explain_column_name'];
            $src_label_column     = $relat_info_row['explain_label_column_name'];
            $src_safe_columns_str = $relat_info_row['explain_ext_columns'];
            $src_safe_columns     = explode(',', $src_safe_columns_str);
            $val_column           = $relat_info_row['index_column_name'];
            $relat_map            = ['infos' => [], 'labels' => []];

            if ($relat_info_row['ext_filter_sql'])
            {
                $filter->setBySql($relat_info_row['ext_filter_sql']);
            }
            $relat_pks = [];
            foreach ($query_all_list as $queryed_row)
            {
                if (!empty($relat_info_row['ext_filter_sql']))
                {
                    if (!$filter->isSave($queryed_row))
                    {
                        continue;
                    }
                }
                if (!is_null($queryed_row[$val_column]))
                {
                    $src_vals[]  = $queryed_row[$relat_info_row['index_column_name']];
                    $relat_pks[] = $queryed_row[$this->main_table_model->pk_key];
                }
            }
            if (count($src_vals) === 0)
            {
                continue;
            }

            $tmp_relat_res = (new static())->setTable($src_db, $src_table)->setAttrs([$src_val_column => array_unique($src_vals)])->query();
            if (count($tmp_relat_res['dataRows']))
            {
                foreach ($tmp_relat_res['dataRows'] as $tmp_res_row)
                {
                    $src_val = $tmp_res_row[$src_val_column];
                    if (isset($tmp_res_row[$src_label_column]))
                    {
                        $relat_map['labels'][$src_val] = $tmp_res_row[$src_label_column];
                    }
                    $tmp_ar = [];
                    foreach ($src_safe_columns as $src_safe_column)
                    {
                        $tmp_ar[$src_safe_column] = $tmp_res_row[$src_safe_column];
                    }
                    $relat_map['infos'][$src_val] = $tmp_ar;
                }
            }


            foreach ($query_all_list as $i => $tmp_row)
            {
                if (in_array($tmp_row[$this->main_table_model->pk_key], $relat_pks))
                {
                    $query_all_list[$i]['__explain'][$val_column] = [
                        'label' => isset($relat_map['labels'][$tmp_row[$val_column]]) ? $relat_map['labels'][$tmp_row[$val_column]] : false,
                        'info'  => isset($relat_map['infos'][$tmp_row[$val_column]]) ? $relat_map['infos'][$tmp_row[$val_column]] : new \stdClass(),
                    ];
                }
                else
                {
                    if (empty($query_all_list[$i]['__explain'][$val_column]))
                    {
                        $query_all_list[$i]['__explain'][$val_column] = [
                            'label' => false,
                            'info'  => new \stdClass(),
                        ];
                    }

                }

            }

        }
        return $query_all_list;
    }

    public function appendRelatData($query_all_list)
    {

        foreach ($this->relation_models as $relation)
        {
            $left_table_index_field_vals = [];
            $left_val_2_row_i_map        = [];
            foreach ($query_all_list as $query_row_i => $queryed_row)
            {
                if (!is_null($queryed_row[$relation->left_table_index_field]))
                {
                    $left_val                      = $queryed_row[$relation->left_table_index_field];
                    $left_table_index_field_vals[] = $left_val;
                    if (!isset($left_val_to_row_i_map[$left_val]))
                    {
                        $left_val_to_row_i_map[$left_val] = [];
                    }
                    $left_val_2_row_i_map[$left_val][] = $query_row_i;
                    if (!isset($queryed_row['__relat']))
                    {
                        $query_all_list[$query_row_i]['__relat'] = [];
                    }
                    $query_all_list[$query_row_i]['__relat'][$relation->relation_res_key] = [];
                }
            }
            Sys::app()->addLog($left_table_index_field_vals, '$left_table_index_field_vals');
            if (count($left_table_index_field_vals) === 0)
            {
                continue;
            }
            $left_table_index_field_vals     = array_unique($left_table_index_field_vals);
            $left_table_index_field_vals_str = join(',', $left_table_index_field_vals);//没错，必须是 int


            $sql_selcet_parts = [];
            $sql_join_parts   = [];
            $sql_where_parts  = [];
            $bind_values      = [];

            $sql_selcet_parts[] = "{$relation->relation_left_field} as _left_val";
            $sql_selcet_parts[] = "{$relation->right_table_name}.{$relation->right_table_label_field} as _label";
            if (strlen($relation->right_table_info_fields))
            {
                $tmp_ar = explode(',', $relation->right_table_info_fields);
                foreach ($tmp_ar as $tmp_str)
                {
                    $sql_selcet_parts[] = "{$relation->right_table_name}.{$tmp_str}";
                }
            }

            if (strlen($relation->relation_ext_field))
            {
                $sql_join_parts[] = " and  {$relation->relation_table_name}.`{$relation->relation_ext_field}`='{$relation->relation_ext_field_val}' ";// 没错，这种扩展值，只接受string
            }
            $sql_select_str    = join(',', array_unique($sql_selcet_parts));
            $sql_left_join_str = join(' ', $sql_join_parts);


            $query_sql = "select {$sql_select_str} from {$relation->relation_table_name} left join {$relation->right_table_name} on {$relation->relation_table_name}.{$relation->relation_right_field}={$relation->right_table_name}.{$relation->right_table_index_field} {$sql_left_join_str} where {$relation->relation_table_name}.{$relation->relation_left_field} in ({$left_table_index_field_vals_str})";


            $relation_rows = DbDbConf::model()->findOneByWhere(['db_code' => $this->dbconf_name])->getConfDbConnect()->setText($query_sql)->queryAll();
            if (count($relation_rows))
            {
                if ($relation->relation_type === DbRelation::HAS_ONE)
                {
                    foreach ($relation_rows as $relation_row)
                    {
                        $queryed_left_val = $relation_row['_left_val'];
                        if (isset($left_val_2_row_i_map[$queryed_left_val]))
                        {
                            foreach ($left_val_2_row_i_map[$queryed_left_val] as $left_queryed_row_index)
                            {
                                $query_all_list[$left_queryed_row_index]['__relat'][$relation->relation_res_key] = $relation_row;
                            }
                        }
                        else
                        {
                            //应该报错
                        }

                    }
                }
                else if ($relation->relation_type === DbRelation::HAS_MANY)
                {
                    foreach ($relation_rows as $relation_row)
                    {
                        $queryed_left_val = $relation_row['_left_val'];
                        if (isset($left_val_2_row_i_map[$queryed_left_val]))
                        {
                            foreach ($left_val_2_row_i_map[$queryed_left_val] as $left_queryed_row_index)
                            {
                                $query_all_list[$left_queryed_row_index]['__relat'][$relation->relation_res_key][] = $relation_row;
                            }
                        }
                        else
                        {
                            //应该报错
                        }

                    }
                }
                else
                {

                }
            }
        }
        return $query_all_list;
    }


    public function getInfo()
    {
        $this->table_model     = $this->getBizTableInfo();
        $this->column_models   = $this->getBizTableColumns();
        $this->relation_models = $this->getBizTableRelations();


        return [
            'table'     => $this->table_model->getOpenInfo(),
            'columns'   => array_map(function (DbColumn $column_model) { return $column_model->getOpenInfo(); }, $this->column_models),
            'relations' => array_map(function (DbRelation $column_model) { return $column_model->getAllInfo(); }, $this->relation_models)
        ];


    }

    /**
     * @return static
     * @throws \models\common\error\AdvError
     */
    public function getBizTableInfo()
    {
        return DbTable::model()->findOneByWhere(['dbconf_name' => $this->dbconf_name, 'table_name' => $this->table_name, 'is_ok' => Opt::isOk]);
    }

    /**
     * @return \models\common\db\ORM[]|DbColumn[]
     * @throws \models\common\error\AdvError
     */
    public function getBizTableColumns()
    {
        return DbColumn::model()->addSort('column_sn', 'desc')->findAllByWhere(['dbconf_name' => $this->dbconf_name, 'table_name' => $this->table_name, 'is_ok' => Opt::isOk]);
    }

    /**
     * @return \models\common\db\ORM[]|DbRelation[]
     * @throws \models\common\error\AdvError
     */
    public function getBizTableRelations()
    {
        return DbRelation::model()->addSort('id', 'desc')->setLimit(0, 10000)->findAllByWhere(['dbconf_name' => $this->dbconf_name, 'left_table_name' => $this->table_name, 'is_ok' => Opt::isOk]);
    }


    /**
     * @param DbTable $dbTable
     * @return static
     */
    public function setBizTableModel(DbTable $dbTable)
    {
        $this->main_table_model = $dbTable;
        return $this;
    }

    public function getOpenInfo()
    {
        return [
            'title'       => $this->title,
            'dbconf_name' => $this->dbconf_name,
            'table_name'  => $this->table_name,
            'remark'      => $this->remark,
            'pk_key'      => $this->pk_key,
            //'orm_class'   => $this->orm_class,
            //'is_ok'       => $this->is_ok,
            'read_roles'  => $this->getJsondecodedValue($this->read_roles, 'array'),
            'all_roles'   => $this->getJsondecodedValue($this->all_roles, 'array'),
            'create_time' => $this->create_time,
        ];
    }


    public function checkReadAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'read_roles', false);
    }

    public function checkUpdateAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'update_roles', false);
    }


    public function checkAddRowAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'add_roles', false);
    }

    public function checkAllAccess(Admin $user)
    {
        return $this->checkAccess($user->role_codes, 'all_roles', false);
    }


    public function checkAccess($user_roles, $access_field, $empty_as_access = true)
    {
        if (is_null($this->$access_field))
        {
            Sys::app()->addLog("table_conf_check_access_{$access_field} is null");
            return $empty_as_access ? true : false;
        }
        $access_roles = $this->getJsondecodedValue($this->$access_field, 'array');
        if (count($access_roles) === 0)
        {
            Sys::app()->addLog([$access_roles, $this->$access_field], "table_conf_check_access_{$access_field} empty array");
            return $empty_as_access ? true : false;
        }
        $intersect_roles = array_intersect($user_roles, $access_roles);
        if (count($intersect_roles) === 0)
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "table_conf_check_access_fail_{$access_field} array_intersect");
            return false;
        }
        else
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "table_conf_check_access_ok_{$access_field} array_intersect");
            return true;
        }
    }

    public static function replaceFakeTableName($table_name)
    {
        if (isset(Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name]))
        {
            $table_name = Sys::app()->params['sys_setting']['db']['tableNameFakeCode'][$table_name];
        }
        return $table_name;
    }

}
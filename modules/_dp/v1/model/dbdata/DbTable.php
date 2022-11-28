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


    private $sort_key  = '';
    private $sort_type = '';
    private $sort_map  = [];

    private $limit_offset = 0;
    private $limit_size   = 20;
    private $page_index   = 0;

    private $ext_sql_opts = [];

    public static $db_connects = [];


    /**
     * @param $dbconf_name
     * @param $table_name
     * @return static|array
     * @throws \models\common\error\AdvError
     */
    public function setTable($dbconf_name, $table_name)
    {
        $this->_dbconf_name = $dbconf_name;
        $this->_table_name  = $table_name;

        $this->main_table_model = $this->findOneByWhere(['table_name' => $this->_table_name, 'dbconf_name' => $this->_dbconf_name, 'is_ok' => 1], false);
        if (empty($this->main_table_model))
        {
            throw new \Exception(" 表不存在 {$this->_dbconf_name}.{$this->_table_name}");
        }
        $column_tn     = DbColumn::$tableName;
        $column_models = DbColumn::model()->findAllByWhere([
            'table_name'  => $this->_table_name,
            'dbconf_name' => $this->_dbconf_name,
            'is_ok'       => Opt::isOk
        ]);

        if (empty($column_models))
        {
            throw new \Exception('字段并未纳入管理');
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
        $this->attrs = $attrs;
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
            if ($this->dbconf_name === 'fast_bg')
            {
                self::$db_connects[$this->dbconf_name] = $this->getDbConnect();
            }
            else
            {
                self::$db_connects[$this->dbconf_name] = DbDbConf::model()->findOneByWhere(['is_ok' => Opt::YES, 'db_code' => $this->dbconf_name])->getConfDbConnect();
            }
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
        $db    = $this->getDbconfConnect();
        $where = '';
        $bind  = [];
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
                        $tmp_ar[] = " {$this->_table_name}.`{$key}` like :{$key} ";
                        //$val             = substr($val, 5);
                        $bind[":{$key}"] = substr($val, 5);
                    }
                    else
                    {
                        $tmp_ar[]        = " {$this->_table_name}.`{$key}`=:{$key} ";
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
                    $tmp_ar[] = " {$this->_table_name}.`{$key}` in ($tmp_str)";
                }
                else
                {
                    throw new \Exception('混进来了什么奇怪的东西' . $key);
                }
            }
            count($tmp_ar) ? $where = " where " . join(' and ', $tmp_ar) : null;
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
        $ext_froms        = [];
        $ext_wheres       = [];
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

        $ext_from = join(' ', $ext_froms);
        //$where    = $where . count($ext_wheres) > 0 ? (($where ? (" and ") : (" where ")) . join(' and ', $ext_wheres)) : '';
        if (count($ext_wheres))
        {
            $where = $where . ($where ? (" and ") : (" where ")) . join(' and ', $ext_wheres);
        }
        $sql     = "select {$column_names_str} from {$this->_table_name} {$ext_from} {$where} {$str_sort} {$str_limit}";
        $sql_cnt = "select count({$this->_table_name}.`{$this->main_table_model->pk_key}`) from {$this->_table_name} {$ext_from} {$where} ";
        $list    = $db->setText($sql)->bindArray($bind)->queryAll();
        $count   = $db->setText($sql_cnt)->bindArray($bind)->queryScalar();

        if (count($list))
        {
            $relat_tn = DbRelation::$tableName;
            $rows     = $this->getDbConnect()->setText("select * from {$relat_tn} where index_dbconf_name='{$this->_dbconf_name}' and index_table_name='{$this->_table_name}' and is_ok=1;")->queryAll();

            $filter = new SqlFilter();
            foreach ($rows as $relat_info_row)
            {
                $src_vals             = [];
                $src_db               = $relat_info_row['related_dbconf_name'];
                $src_table            = $relat_info_row['related_table_name'];
                $src_val_column       = $relat_info_row['related_column_name'];
                $src_label_column     = $relat_info_row['related_label_column_name'];
                $src_safe_columns_str = $relat_info_row['related_ext_columns'];
                $src_safe_columns     = explode(',', $src_safe_columns_str);
                $val_column           = $relat_info_row['index_column_name'];
                $relat_map            = ['infos' => [], 'labels' => []];

                if ($relat_info_row['ext_filter_sql'])
                {
                    $filter->setBySql($relat_info_row['ext_filter_sql']);
                }
                $relat_pks = [];
                foreach ($list as $queryed_row)
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


                foreach ($list as $i => $tmp_row)
                {
                    if (in_array($tmp_row[$this->main_table_model->pk_key], $relat_pks))
                    {
                        $list[$i]['__relat'][$val_column] = [
                            'label' => isset($relat_map['labels'][$tmp_row[$val_column]]) ? $relat_map['labels'][$tmp_row[$val_column]] : false,
                            'info'  => isset($relat_map['infos'][$tmp_row[$val_column]]) ? $relat_map['infos'][$tmp_row[$val_column]] : new \stdClass(),
                        ];
                    }
                    else
                    {
                        if (empty($list[$i]['__relat'][$val_column]))
                        {
                            $list[$i]['__relat'][$val_column] = [
                                'label' => false,
                                'info'  => new \stdClass(),
                            ];
                        }

                    }

                }

            }


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


    public function getInfo()
    {
        $table_model   = $this->getBizTableInfo();
        $column_models = $this->getBizTableColumns();

        return [
            'table'   => $table_model->getOpenInfo(),
            'columns' => array_map(function ($column_model) { return $column_model->getOpenInfo(); }, $column_models)
        ];


    }

    /**
     * @return static
     * @throws \models\common\error\AdvError
     */
    public function getBizTableInfo()
    {
        return DbTable::model()->findOneByWhere(['dbconf_name' => $this->_dbconf_name, 'table_name' => $this->_table_name, 'is_ok' => Opt::isOk]);
    }

    /**
     * @return \models\common\db\ORM[]|DbColumn[]
     * @throws \models\common\error\AdvError
     */
    public function getBizTableColumns()
    {
        return DbColumn::model()->addSort('column_sn', 'desc')->findAllByWhere(['dbconf_name' => $this->_dbconf_name, 'table_name' => $this->_table_name, 'is_ok' => Opt::isOk]);
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

}
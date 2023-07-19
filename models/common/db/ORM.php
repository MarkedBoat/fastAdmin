<?php

namespace models\common\db;

use models\common\error\AdvError;
use models\common\sys\Sys;
use mysql_xdevapi\Exception;


class ORM
{
    private $__outer_data = [];//对外  get/set 用的都是它，含有脏数据
    private $__inner_data = [];//只代表从数据库读出来的， 绝对可信，每次从数据库刷新的时候，必然更改outer_data
    public  $dbname_flag  = '';

    private $_db_connect;
    private $_on_duplicate_key_update_attrs    = [];
    private $_on_duplicate_key_reload_by_attrs = [];
    private $_insert_ignore                    = false;
    private $_limit                            = 50;
    private $_offset                           = 0;
    private $_orders                           = [];
    private $_errors                           = [];
    private $_opt_count_total                  = false;
    private $_count_total_res                  = 0;
    private $_open_page_index                  = 0;
    private $_page_size                        = 0;
    private $_page_total                       = 0;

    public static $_fields_str;
    public static $pk           = '';
    public static $tableName    = '';
    public static $field_config = [];


    public function __construct()
    {
        static::initInfo();
    }

    public function initInfo()
    {
        if (is_null(static::$_fields_str))
        {
            $fields = [];
            foreach (static::$field_config as $field => $info)
            {
                $fields[] = "`{$field}`";
            }
            static::$_fields_str = join(',', $fields);
        }
    }

    /**
     * @return static|ORM
     */
    public static function model()
    {
        return new static();
    }

    public function getPkFieldName()
    {
        return static::$pk;
    }

    public function getTableName()
    {
        return static::$tableName;
    }


    public function getDbConfName()
    {
        return '';
    }

    /**
     * @return MysqlCnn
     * @throws \Exception
     */
    public function getDbConnect()
    {
        if (is_null($this->_db_connect))
        {
            $this->_db_connect = Sys::app()->db(static::getDbConfName());
        }
        return $this->_db_connect;
    }

    public final function setDbNameFlag($flag)
    {
        $this->dbname_flag = $flag;
        return $this;
    }

    /**
     * @param $db_data
     * @return static
     */
    public static function loadByData($db_data)
    {
        $model               = new static();
        $model->__inner_data = $db_data;
        $model->loadData($db_data);
        return $model;
    }


    /**
     * @param $pk
     * @param $throw_error
     * @return bool|static
     * @throws \Exception
     */
    public function findByPk($pk, $throw_error = true)
    {
        return $this->findOneByWhere([static::$pk => $pk], $throw_error);
    }

    public function __set($name, $val)
    {
        $this->__outer_data[$name] = $val;
    }

    public function __get($name)
    {
        return isset($this->__outer_data[$name]) ? $this->__outer_data[$name] : null;
    }


    /**
     * @param bool $throw_error
     * @param bool $reload_data
     * @return static|bool
     * @throws \Exception
     */
    public final function insert($throw_error = true, $reload_data = true)
    {
        $bind = [];
        $sqls = [];
        foreach (static::$field_config as $field => $config)
        {
            if (isset($this->$field))
            {
                $sqls[':' . $field] = "`$field`=:$field";
                if ($config['db_type'] === 'json' && is_array($this->$field))
                {
                    $bind[':' . $field] = json_encode($this->$field);
                }
                else
                {
                    $bind[':' . $field] = $this->$field;
                }
            }
        }
        $pk            = static::$pk;
        $insert_ignore = '';
        if ($this->_insert_ignore)
        {
            $insert_ignore        = 'ignore';
            $this->_insert_ignore = false;
        }

        if (count($bind) === 0)
        {
            if ($throw_error)
            {
                AdvError::logError("ORM.createOrm fail tableName:" . static::$tableName . ' ，no data need save');
            }
            return false;
        }


        try
        {
            if (count($this->_on_duplicate_key_update_attrs))
            {
                $update_strs = [];
                $update_bind = [];
                foreach (static::$field_config as $field => $config)
                {
                    if (isset($this->_on_duplicate_key_update_attrs[$field]))
                    {
                        $update_strs[] = "`{$field}`=:u_{$field}";

                        if ($config['db_type'] === 'json' && is_array($this->_on_duplicate_key_update_attrs[$field]))
                        {
                            $update_bind[":u_{$field}"] = json_encode($this->_on_duplicate_key_update_attrs[$field]);
                        }
                        else
                        {
                            $update_bind[":u_{$field}"] = $this->_on_duplicate_key_update_attrs[$field];
                        }
                    }

                }
                $this->_on_duplicate_key_update_attrs = [];


                $sql = "INSERT  INTO " . $this->getTableName() . ' SET ' . join(',', $sqls) . ' on duplicate key update ' . join(',', $update_strs) . ';';
                $cmd = $this->getDbConnect();
                $res = $cmd->setText($sql)->bindArray(array_merge($bind, $update_bind))->execute();
                if ($res)
                {
                    $this->$pk = $cmd->lastInsertId();
                }
                if (count($this->_on_duplicate_key_reload_by_attrs) === 0)
                {
                    return !empty($res);
                }
                else
                {
                    $tmp                                     = $this->_on_duplicate_key_reload_by_attrs;
                    $this->_on_duplicate_key_reload_by_attrs = [];
                    return $this->findOneByWhere($res, false);
                }


            }
            else
            {
                $cmd      = $this->getDbConnect()->setText("INSERT {$insert_ignore} INTO " . $this->getTableName() . ' SET ' . join(',', $sqls) . ';')->bindArray($bind);
                $rowCount = $cmd->execute();
                if (empty($rowCount))
                    return false;
                $this->$pk = $cmd->lastInsertId();
            }


        } catch (\Exception $e)
        {
            $err_msg = $e->getMessage();
            if (strstr($err_msg, 'Duplicate entry'))
            {
                $this->_errors[] = 'DuplicateKey';
            }
            if ($throw_error)
            {
                throw new AdvError(AdvError::db_common_error, "ORM.tryInsert fail tableName:" . static::$tableName . ' #' . $e->getMessage(), [$bind]);
            }
            else
            {
                AdvError::logError("ORM.tryInsert fail tableName:" . static::$tableName . ' #' . $e->getMessage(), [$bind]);
                return false;
            }
        }
        if ($reload_data)
        {
            $this->reloadData();
        }
        return $this;

    }


    /**
     * @param bool $throw_error
     * @param bool $reload_data
     * @return static|bool
     * @throws AdvError|\Exception
     */
    public final function update($throw_error = true, $reload_data = true)
    {
        $pk = static::$pk;
        if (empty($this->__inner_data[$pk]))
        {
            throw new AdvError(AdvError::db_common_error, "PK:{$pk} empty,should tryInsert ");
        }
        $bind         = [':old_pk_val' => $this->__inner_data[$pk]];
        $sqls         = [];
        $change_attrs = [];
        foreach (static::$field_config as $field => $config)
        {
            if (isset($this->$field) && (!isset($this->__inner_data[$field]) || $this->$field !== $this->__inner_data[$field]))
            {
                $sqls[':' . $field] = "`$field`=:$field";
                if ($config['db_type'] === 'json' && is_array($this->$field))
                {
                    $bind[':' . $field] = json_encode($this->$field);
                }
                else
                {
                    $bind[':' . $field] = $this->$field;
                }
                $change_attrs[$field] = $this->$field;
            }
        }
        if (count($sqls) === 0)
        {
            if ($throw_error)
            {
                throw  new \Exception('没有数据变动');
            }
            return false;
        }
        try
        {
            $cmd      = $this->getDbConnect()->setText('update ' . $this->getTableName() . ' set ' . join(',', $sqls) . " where `{$pk}`=:old_pk_val")->bindArray($bind);
            $rowCount = $cmd->execute();
        } catch (\Exception $e)
        {
            AdvError::logError("ORM.updateOrm fail tableName:" . static::$tableName, [$e->getMessage(), $bind]);
            if ($throw_error)
            {
                throw  $e;
            }
            return false;
        }
        if ($reload_data)
        {
            $this->reloadData();
        }

        return $this;

    }


    /**
     * @param $db_data
     * @param bool $is_inner_data ，数据不可信的情况下，不要设置成true
     * @return static
     * @throws AdvError
     */
    public function loadData($db_data, $is_inner_data = false)
    {
        $pk = static::$pk;
        if ($is_inner_data)
        {
            foreach (static::$field_config as $field => $config)
            {
                if (isset($db_data[$field]))
                {
                    if (strstr($config['db_type'], 'int'))
                    {
                        $this->$field               = intval($db_data[$field]);
                        $this->__inner_data[$field] = $this->$field;
                    }
                    else if (strstr($config['db_type'], 'json'))
                    {

                        if (is_string($db_data[$field]))
                        {
                            $this->$field               = $db_data[$field] ? json_decode($db_data[$field], true) : [];
                            $this->__inner_data[$field] = $db_data[$field];
                        }
                        else if (is_array($db_data[$field]))
                        {
                            $this->$field               = $db_data[$field];
                            $this->__inner_data[$field] = json_encode($db_data[$field]);
                        }
                        else
                        {
                            throw  new AdvError(AdvError::data_error);
                        }
                    }
                    else
                    {
                        $this->$field               = $db_data[$field];
                        $this->__inner_data[$field] = $db_data[$field];

                    }
                }
            }
        }
        else
        {
            foreach (static::$field_config as $field => $config)
            {
                if (isset($db_data[$field]))
                {

                    if (strstr($config['db_type'], 'int'))
                    {
                        $this->$field = intval($db_data[$field]);
                    }
                    else if (strstr($config['db_type'], 'json'))
                    {

                        if (is_array($db_data[$field]))
                        {
                            $this->$field = $db_data[$field];
                        }
                        else if (is_string($db_data[$field]))
                        {
                            if (empty($db_data[$field]))
                            {
                                $this->$field = [];
                            }
                            else
                            {
                                $this->$field = json_decode($db_data[$field], true);
                                if (is_null($this->$field))
                                {
                                    throw  new AdvError(AdvError::data_error, '还原json数据错误', [$db_data, $field]);
                                }

                            }
                        }
                        else if (is_null($db_data[$field]))
                        {
                            $this->$field = [];
                        }
                        else
                        {
                            throw  new AdvError(AdvError::data_error, '还原json数据错误', [$db_data, $field]);
                        }
                    }
                    else
                    {
                        $this->$field = $db_data[$field];
                    }
                }
            }
        }

        return $this;
    }


    /**
     * @return static
     * @throws AdvError|\Exception
     */
    public function reloadData()
    {
        $this->initInfo();

        $pk         = static::$pk;
        $tn         = static::$tableName;
        $fields_str = static::$_fields_str;
        $res        = static::getDbConnect()->setText("select {$fields_str} from {$tn} where `{$pk}`=:pk limit 1")->bindArray([':pk' => $this->$pk])->queryRow();
        if (empty($res))
        {
            throw new AdvError(AdvError::data_not_exist, '查不到信息', [
                'table' => $tn,
                'pk'    => $pk,
                'outer' => $this->__outer_data,
                'sql'   => "select {$fields_str} from {$tn} where `{$pk}`=:pk limit 1",
                'bind'  => [':pk' => $this->$pk]
            ]);
        }
        return $this->loadData($res, true);
    }


    /**
     * @param $condition
     * @param $throw_error
     * @return $this|bool
     * @throws AdvError
     */
    public function findOneByWhere($condition, $throw_error = true)
    {

        $this->initInfo();
        $tn          = static::$tableName;
        $fields_str  = static::$_fields_str;
        $where_strs  = [];
        $where_binds = [];
        foreach ($condition as $field => $v)
        {
            $where_strs[]             = "`{$field}`=:{$field}";
            $where_binds[":{$field}"] = $v;
        }
        $where_str = join(' and ', $where_strs);
        $res       = static::getDbConnect()->setText("select {$fields_str} from {$tn} where {$where_str} limit 1")->bindArray($where_binds)->queryRow();


        if (empty($res))
        {
            if ($throw_error)
            {
                throw new AdvError(AdvError::data_not_exist, '查不到信息', [static::$tableName, $condition]);
            }
            return false;
        }
        return $this->loadData($res, true);
    }


    /**
     * @param $condition
     * @param bool $throw_error
     * @return static[]
     * @throws \Exception
     */
    public function findAllByWhere($condition, $throw_error = true)
    {

        $this->initInfo();
        $tn          = static::$tableName;
        $fields_str  = static::$_fields_str;
        $where_strs  = [];
        $where_binds = [];
        $tmp_key     = 0;
        foreach ($condition as $field => $v)
        {
            Sys::app()->addLog([$v], 'sub_' . $field);

            if (is_array($v))
            {
                if (count($v) === 0)
                {
                    throw new AdvError(AdvError::code_error, 'in查询不能适用空数组');
                }
                $in_ar = [];
                foreach ($v as $tmp_i => $v2)
                {
                    $in_ar[]                           = ":{$field}_{$tmp_i}";
                    $where_binds[":{$field}_{$tmp_i}"] = $v2;
                }
                $tmp_str      = join(',', $in_ar);
                $where_strs[] = "`{$field}` in ({$tmp_str})";
            }
            else if (strstr($field, '->'))
            {
                $where_strs[]                   = "{$field}=:tmp_{$tmp_key}";
                $where_binds[":tmp_{$tmp_key}"] = $v;
                $tmp_key++;
            }
            else
            {
                if (is_string($v) && substr($v, 0, 5) === 'like:')
                {
                    $where_strs[]             = "`{$field}` like :{$field}";
                    $where_binds[":{$field}"] = substr($v, 5);
                }
                else
                {
                    $where_strs[]             = "`{$field}`=:{$field}";
                    $where_binds[":{$field}"] = $v;
                }

            }

        }
        $order_by = '';
        if (count($this->_orders))
        {
            $order_by = ' order by ' . join(',', $this->_orders);
        }
        $limit_str = "{$this->_offset},{$this->_limit} ";
        if (empty($where_binds))
        {
            $rows = static::getDbConnect()->setText("select {$fields_str} from {$tn} {$order_by} limit {$limit_str}")->bindArray($where_binds)->queryAll();
        }
        else
        {
            $where_str = join(' and ', $where_strs);

            $rows = static::getDbConnect()->setText("select {$fields_str} from {$tn} where {$where_str}  {$order_by} limit {$limit_str}")->bindArray($where_binds)->queryAll();
            //$rows = static::getDbConnect()->setText("select {$fields_str} from {$tn} where {$where_str}")->bindArray($where_binds)->debugInfo(true);

        }
        if ($this->_opt_count_total)
        {
            $pk_field = static::$pk;
            if (empty($where_binds))
            {
                $this->_count_total_res = static::getDbConnect()->setText("select count({$pk_field}) from {$tn} ")->queryScalar();
            }
            else
            {
                $where_str = join(' and ', $where_strs);

                $this->_count_total_res = static::getDbConnect()->setText("select count({$pk_field}) from {$tn} where {$where_str} ")->bindArray($where_binds)->queryScalar();
            }
            $this->_opt_count_total = false;
            $this->_page_total      = ceil($this->_count_total_res / $this->_page_size);

        }
        else
        {
            $this->_count_total_res = 0;
            $this->_page_total      = 0;
            $this->_open_page_index = 0;
            $this->_page_size       = 0;
        }
        $this->_limit  = 50;
        $this->_offset = 0;
        $this->_orders = [];


        if (empty($rows))
        {
            return [];
        }
        $models = [];
        foreach ($rows as $row)
        {
            $models[] = (new static())->loadData($row, true);
        }
        return $models;

    }

    /**
     * 获取配置
     * @return array
     */
    public function getFieldMap()
    {
        return static::$field_config;
    }

    /**
     * 获取所有字段
     * @return mixed
     */
    public function geFieldsString()
    {
        return static::$_fields_str;
    }

    /**
     * @param array $update_attrs
     * @param array $reload_by_attrs
     * @return static
     */
    public function setOnDuplicateKeyUpdate($update_attrs = [], $reload_by_attrs = [])
    {
        $this->_on_duplicate_key_update_attrs = $update_attrs;
        return $this;
    }

    /**
     * 在插入的时候  忽略错误
     * @param bool $yes_or_no
     * @return static
     */
    public function setInsertIgnore($yes_or_no = true)
    {
        $this->_insert_ignore = $yes_or_no;
        return $this;
    }

    public function getOuterDataArray()
    {
        $res = [];
        foreach (static::$field_config as $field => $config)
        {
            if (isset($this->$field))
            {
                $res[$field] = $this->$field;
            }
        }
        return $res;
    }

    public function getJsondecodedValue($db_value, $type)
    {
        if ($type === 'object')
        {
            return is_array($db_value) ? $db_value : (strval($db_value) ? json_decode($db_value, true) : ((object)array()));
        }
        else
        {
            return is_array($db_value) ? $db_value : (strval($db_value) ? json_decode($db_value, true) : []);
        }
    }

    /**
     * @param $offset
     * @param $limit
     * @return static
     */
    public function setLimit($offset, $limit)
    {
        $this->_limit  = $limit;
        $this->_offset = $offset;
        return $this;
    }

    /**
     * @param int $open_page_index 实际上 内部用的 -1 ，内部是0开始
     * @param $page_size
     * @return static
     */
    public function setPage($open_page_index, $page_size)
    {
        $this->_limit           = $page_size;
        $this->_page_size       = $page_size;
        $this->_open_page_index = $open_page_index > 0 ? $open_page_index : 0;
        $this->_offset          = ($this->_open_page_index - 1) * $page_size;
        return $this;
    }

    /**
     * @param $field
     * @param $type
     * @return $this
     * @throws AdvError
     */
    public function addSort($field, $type)
    {
        if (!in_array($type, ['asc', 'desc']))
        {
            throw new AdvError(AdvError::code_error);
        }
        if (isset(self::$field_config[$field]))
        {
            throw new AdvError(AdvError::code_error);
        }
        $this->_orders[] = "`{$field}` {$type}";
        return $this;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 设置统计任务
     * @param $status
     * @return static
     */
    public function setOptCountTotalStatus($status)
    {
        $this->_opt_count_total = $status;
        return $this;
    }

    public function getCountRes()
    {
        return $this->_count_total_res;
    }

    public function getPageInfo()
    {
        return [
            'page_index' => $this->_open_page_index,
            'page_size'  => $this->_page_size,
            'page_total' => $this->_page_total,
            'list_total' => $this->_count_total_res,
        ];
    }

}

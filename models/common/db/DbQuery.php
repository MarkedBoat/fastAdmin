<?php

namespace models\common\db;

use app\BaseController;
use app\models\Sys;
use models\common\error\AdvError;
use think\console\output\formatter\Stack;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;
use think\facade\Session;

class DbQuery
{

    const op         = [
        '='      => ['sym' => '=', 'multi' => false],
        'in'     => ['sym' => 'in', 'multi' => true],
        'not_in' => ['sym' => 'not in', 'multi' => true],
        '<'      => ['sym' => '<', 'multi' => false],
        '<='     => ['sym' => '<=', 'multi' => false],
        '>'      => ['sym' => '>', 'multi' => false],
        '>='     => ['sym' => '>=', 'multi' => false],
        '!='     => ['sym' => '!=', 'multi' => false],

    ];
    const string_sym = [
        'eq' => [
            'sym' => '='
        ],
        'lt' => ['sym' => '<'],
        'gt' => ['sym' => '>'],
        'le' => ['sym' => '<='],
        'ge' => ['sym' => '>='],

    ];

    const order = ['asc' => 'asc', 'desc' => 'desc'];

    private $wheres = [];
    private $binds  = [];
    private $orders = [];
    private $limit  = 1;
    private $pageNo = 0;

    private $isDebug = false;

    /**
     * @var ORM
     */
    private $orm;
    private $filed_map = [];


    /**
     * @param ORM $orm_model
     * @return static
     */
    public static function model(ORM $orm_model)
    {
        return new static($orm_model);
    }

    public function __construct(ORM $orm_model)
    {
        $this->orm       = $orm_model;
        $this->filed_map = $orm_model->getFieldMap();
    }

    /**
     * @param $filed
     * @param $op
     * @param $val
     * @return static
     * @throws AdvError
     */
    public function addWhere($filed, $op, $val)
    {
        if (!isset($this->filed_map[$filed]))
        {
            throw  new AdvError(AdvError::db_common_error);
        }
        $right_str = '';
        if ($op['multi'])
        {
            if (is_array($val))
            {
                $right_str = ' (';
                $tmp_strs  = [];
                foreach ($val as $val2)
                {
                    $tmp_strs[] = $this->appendBindVal($filed, $val2);
                }
                $right_str .= join(',', $tmp_strs) . ' )';
            }
            else
            {
                $right_str = $this->appendBindVal($filed, $val);
            }
        }
        else
        {
            $right_str = $this->appendBindVal($filed, $val);
        }
        $this->wheres[] = "`{$filed}` {$op['sym']} {$right_str}";

        return $this;
    }

    public function orderBy($field, $type)
    {
        $this->orders[] = "{$field} {$type}";
        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setCurrPage(int $pageNo)
    {
        $this->pageNo = $pageNo;
        return $this;
    }

    /**
     * 添加绑定词，并且会反馈 bind key
     * <br> !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * <br>注意  thinkPhp的封装  画蛇添足了  ，sql占位符和 bind 的key ，是不一样的，bind key 不带 [ : ]，而sql中是需要的，
     * <br>但是在原生PDO中的是保持一致的
     * <br> !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * @param $field
     * @param $val
     * @return string
     */
    private function appendBindVal($field, $val)
    {
        $len               = count($this->binds);
        $bind_key          = ":{$field}_{$len}";
        $thinkphp_bind_key = "{$field}_{$len}";

        $this->binds[$thinkphp_bind_key] = $val;
        return $bind_key;
    }

    public function queryAll()
    {
        $tn      = $this->orm->getTableName();
        $db_name = $this->orm->getDbName();
        $where   = '';
        if (count($this->wheres))
        {
            $where = ' where ' . join(' and ', $this->wheres);

        }
        $order_by = '';
        if (count($this->orders))
        {
            $order_by = ' order by ' . join(',', $this->orders);
        }
        $limit = '';
        if ($this->limit)
        {
            $pageNo = $this->pageNo < 1 ? $this->pageNo : ($this->pageNo - 1);
            $start  = $this->limit * $pageNo;
            $limit  = " limit {$start},{$this->limit}";
        }
        $sql = "select * from {$db_name}.gz_{$tn} {$where} {$order_by} {$limit}";

        if ((empty($this->limit) || $this->limit > 100) && count($this->wheres) === 0)
        {
            throw new AdvError(AdvError::db_common_error, '别瞎调,把数据库查死了!');
        }
        $pk_key = $this->orm->getPkFieldName();
        return [
            'rows_total' => intval(Db::query("select count({$pk_key}) as cnt from {$db_name}.gz_{$tn} {$where}", $this->binds)[0]['cnt']),
            'page_size'  => $this->limit,
            'page_num'   => $this->pageNo,
            'data_rows'  => Db::query($sql, $this->binds),
            '_debug'     => $this->isDebug ? ['sql' => $sql, 'bind' => $this->binds, '_doc' => $this->getFieldComments()] : false,
        ];
    }

    /**
     * @param $input_map
     * @return static
     * @throws AdvError
     */
    public function tryAcceptCondtions($input_map)
    {
        foreach ($this->filed_map as $field => $config)
        {
            if (isset($input_map[$field]))
            {
                $this->addWhere($field, self::op['='], $input_map[$field]);
            }
            if (isset($input_map["{$field}__lt"]))
            {
                $this->addWhere($field, self::op['<'], $input_map["{$field}__lt"]);
            }
            if (isset($input_map["{$field}__le"]))
            {
                $this->addWhere($field, self::op['<='], $input_map["{$field}__le"]);
            }
            if (isset($input_map["{$field}__gt"]))
            {
                $this->addWhere($field, self::op['>'], $input_map["{$field}__gt"]);
            }
            if (isset($input_map["{$field}__ge"]))
            {
                $this->addWhere($field, self::op['>='], $input_map["{$field}__ge"]);
            }
            if (isset($input_map["{$field}__ne"]))
            {
                $this->addWhere($field, self::op['!='], $input_map["{$field}__ne"]);
            }
            if (isset($input_map["{$field}__in"]))
            {
                $this->addWhere($field, self::op['in'], json_decode($input_map["{$field}__in"], true));
            }

            if (isset($input_map["{$field}__not_in"]))
            {
                $this->addWhere($field, self::op['not_in'], json_decode($input_map["{$field}__not_in"], true));
            }
        }
        if (isset($input_map['_size']))
        {
            $this->limit(intval($input_map['_size']));
        }
        if (isset($input_map['_pn']))
        {
            $this->setCurrPage(intval($input_map['_pn']));
        }
        if (isset($input_map['_sort']) && is_array($input_map['_sort']))
        {
            foreach ($input_map['_sort'] as $filed => $sort_type)
            {
                $this->orderBy($filed, $sort_type);
            }
        }

        if (isset($input_map['_debug']) && $input_map['_debug'] === 'yes')
        {
            $this->isDebug = true;
        }
        else
        {
            $this->isDebug = false;
        }

        if (isset($input_map['_dbname_flag']) && strlen($input_map['_dbname_flag']))
        {
            $this->orm->setDbNameFlag($input_map['_dbname_flag']);
        }
        return $this;
    }


    /**
     * @param $input_map
     * @return static
     * @throws AdvError
     */
    public function tryUpdate($input_map)
    {
        $select_binds = [];
        $update_binds = [];
        $where_strs   = [];

        $pk_field = $this->orm->getPkFieldName();
        $selects  = ["`{$pk_field}`"];

        if (isset($input_map[$pk_field]))
        {
            $len                                = count($select_binds);
            $select_binds["{$pk_field}_{$len}"] = $input_map[$pk_field];
            $where_strs[]                       = "`{$pk_field}`=:{$pk_field}_{$len}";

            $update_binds["{$pk_field}_{$len}"] = $input_map[$pk_field];

        }
        else if (isset($input_map["{$pk_field}__in"]))
        {
            $vals       = json_decode($input_map["{$pk_field}__in"], true);
            $where_strs = [" `{$pk_field}` in ("];
            foreach ($vals as $val)
            {
                $len                                = count($select_binds);
                $select_binds["{$pk_field}_{$len}"] = $val;
                $update_binds["{$pk_field}_{$len}"] = $val;
                $where_strs[]                       = ":{$pk_field}_{$len}";
            }
            $where_strs[] = ")";

        }
        else
        {
            throw new AdvError(AdvError::request_param_empty, 'update 时 ,无主键:' . $pk_field);
        }


        $this->isDebug = isset($input_map['_debug']) && $input_map['_debug'] === 'yes';


        if (isset($input_map['_dbname_flag']) && strlen($input_map['_dbname_flag']))
        {
            $this->orm->setDbNameFlag($input_map['_dbname_flag']);
        }

        $sets = [];
        foreach ($input_map as $key => $val)
        {
            if (isset($this->filed_map[$key]))
            {
                $len                              = count($update_binds);
                $sets[]                           = "`{$key}`=:{$key}_{$len}";
                $selects[]                        = "`{$key}`";
                $thinkphp_bind_key                = "{$key}_{$len}";
                $update_binds[$thinkphp_bind_key] = $val;
            }
        }
        if (empty($where_strs))
        {
            throw new AdvError(AdvError::db_common_error, '别瞎调!');
        }

        $tn         = $this->orm->getTableName();
        $db_name    = $this->orm->getDbName();
        $where_str  = join('', $where_strs);
        $select_str = join(',', array_unique($selects));
        $op_flag    = date('YmdHis') + rand(100000, 1000000);
        $select_sql = "select {$select_str} from {$db_name}.gz_{$tn} where {$where_str}";
        $rows       = Db::query($select_sql, $select_binds);
        Sys::app()->logInfo("DbQuery.update.bak op_flag:{$op_flag} ", ['sql' => $select_sql, 'bind' => $select_binds, 'res' => $rows, 'input' => $input_map]);


        $set_str    = join(',', $sets);
        $update_sql = "update {$db_name}.gz_{$tn} set  {$set_str}  where {$where_str}";
        $res        = Db::execute($update_sql, $update_binds);
        Sys::app()->logInfo("DbQuery.update.res op_flag:{$op_flag} ", ['sql' => $select_sql, 'bind' => $select_binds, 'res' => $res]);

        $update_rows = Db::query($select_sql, $select_binds);
        Sys::app()->logInfo("DbQuery.update.review op_flag:{$op_flag} ", ['sql' => $select_sql, 'bind' => $select_binds, 'res' => $rows]);


        return $this->isDebug ? [
            'updated' => $update_rows,
            'update'  => [
                'sql'  => $update_sql,
                'bind' => $update_binds
            ],
            'select'  => [
                'sql'  => $select_sql,
                'bind' => $select_binds
            ]
        ] : ['updated' => $update_rows];
    }


    /**
     * @param $input_map
     * @return static
     * @throws AdvError
     */
    public function tryInsert($input_map)
    {


        $pk_field = $this->orm->getPkFieldName();

        $this->isDebug = isset($input_map['_debug']) && $input_map['_debug'] === 'yes';

        if (isset($input_map['_dbname_flag']) && strlen($input_map['_dbname_flag']))
        {
            $this->orm->setDbNameFlag($input_map['_dbname_flag']);
        }

        $attrs = [];
        foreach ($input_map as $key => $val)
        {
            if (isset($this->filed_map[$key]))
            {
                $attrs[$key] = $val;
            }
        }

        $tn      = $this->orm->getTableName();
        $db_name = $this->orm->getDbName();

        $op_flag = date('YmdHis') + rand(100000, 1000000);

        $last_insert_id = Db::table("{$db_name}.gz_{$tn}")->insertGetId($attrs);
        Sys::app()->logInfo("DbQuery.insert.res op_flag:{$op_flag} ", ['atts' => $attrs, 'last_insert_id' => $last_insert_id]);

        $insert_rows = Db::query("select * from {$db_name}.gz_{$tn} where `{$pk_field}`={$last_insert_id}");
        Sys::app()->logInfo("DbQuery.update.review op_flag:{$op_flag} ", ['insert_rows' => $insert_rows]);


        return $insert_rows;
    }

    public function getFieldComments()
    {
        $tn         = $this->orm->getTableName();
        $table_info = Db::query("select table_name,  table_label, table_detail from dbdict_tables where table_name='gz_{$tn}' limit 1")[0];
        return [
            'table_info'  => $table_info,
            'colums_info' => $table_info = Db::query("select  field_name, field_label, field_detail from dbdict_table_fields where table_name='gz_{$tn}' "),
        ];
    }
}

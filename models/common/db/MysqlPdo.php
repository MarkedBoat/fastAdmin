<?php

namespace models\common\db;

//use \console\CConsole;
//use \models\ConsoleError;

use models\common\sys\Sys;

class MysqlPdo extends \PDO
{
    private $commandText = '';
    private $cmd         = null;
    private $prefix      = '';
    private $bindData    = [];
    private $readOnly    = false;
    public  $cts         = '';
    private $cfg         = [];
    private $dbname      = '';

    /**
     * @param $config
     * @param bool $isAlive
     * @return MysqlPdo/PDO
     */
    public static function configDb($config, $isAlive = false)
    {

        $opt = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}",
            \PDO::ATTR_TIMEOUT            => 10,
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT         => true,
            \PDO::ATTR_EMULATE_PREPARES   => true
        );
        try
        {
            //if(strstr($config['connectionString'],'www'))throw new \Exception('xxx');
            //var_dump($config['connectionString']);
            $dsn   = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
            $model = new MysqlPdo($dsn, $config['username'], $config['password'], array_merge($config['attributes'], $opt));
        } catch (\Exception $e)
        {
            Sys::app()->addLog([$config, $opt], '数据库配置');
            Sys::app()->interruption()->setMsg('操作失败' . '数据库链接失败' . $e->getMessage() . $e->getCode())->setDebugData($config)->outError();
        }

        $model->prefix   = isset($config['prefix']) ? trim($config['prefix']) : '';
        $model->readOnly = isset($config['readOnly']) ? $config['readOnly'] : false;
        $model->cts      = date('Y-m-d H:i:s', time());
        $model->cfg      = $config;
        $model->dbname   = $config['dbname'];
        return $model;
    }

    public function __get($attr)
    {
        if (isset($this->$attr))
            return $this->$attr;
        throw new \Exception('could find attr');
    }


    /**
     * @param $commandText
     * @return MysqlPdoCmd
     */
    public function setText($commandText)
    {
        return new MysqlPdoCmd($this, $commandText);
    }

    /**
     * 批量查询
     * @param string $sql_tpl 用 {VAR} 代替 变量
     * @param array $vals 要查的值，如果是字符串，需先引号包起来
     * @param int $batch_size 切成多大一块去查询
     * @param array $bindArray
     * @return array
     */
    public function batchQueryAll($sql_tpl, $vals, $batch_size = 100, $bindArray = [])
    {
        $valss = array_chunk($vals, $batch_size);
        $res   = [];
        foreach ($valss as $i => $vals)
        {
            $tmp_array = [];
            $keys      = [];
            foreach ($vals as $j => $val)
            {
                $key             = ":_v_{$j}";
                $tmp_array[$key] = $val;
                $keys[]          = $key;
            }
            $str = join(',', $keys);
            $sql = str_replace('{VAR}', $str, $sql_tpl);
            $res = array_merge($res, $this->setText($sql)->bindArray(array_merge($tmp_array, $bindArray))->queryAll());
        }
        return $res;
    }


    /**
     * 循环批量查询 批量查询
     * @param string $sql 用 {VAR} 代替 变量，但是 sql 需要写好顺序
     * <br> select * from xxxxx where  create_time>=:start_date and create_time<:end_date and   `{$fetch_key}`>:start_val
     * <br> 至于是  > 还是 < ，都在sql_tpl里面写好，不是由参数转化,保持可读性，默认是  从小到大，需要反转由
     * <br>  !!!!!!!!! 必须是含 :start_val
     * @param string $fetch_field 依据哪个field 查询
     * @param int $start_val 起始值,！！！！应该是不被查询出来的值，理论上是上一段数据的结尾，实在不行，可以用  -1 的方法处理
     * @param int $limit 查询总数，到了多少条为止,注意会过查
     * @param array $bindArray
     * @return array
     */
    public function fetchAllByField($sql, $fetch_field, $start_val, $limit = 1000, $bindArray = [])
    {
        $cmd = $this->setText($sql);
        $res = [];
        $i   = 0;
        while (true)
        {
            $tmp_rows = $cmd->bindArray(array_merge([':start_val' => $start_val], $bindArray))->queryAll();
            if (empty($tmp_rows))
            {
                break;
            }
            foreach ($tmp_rows as $tmp_row)
            {
                $start_val = $tmp_row[$fetch_field];
                $res[]     = $tmp_row;
                $i++;
                if ($limit && $i >= $limit)
                {
                    break;
                }
            }
        }

        return $res;
    }


    public function beginTransaction()
    {
        $this->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        parent::beginTransaction();
    }

}




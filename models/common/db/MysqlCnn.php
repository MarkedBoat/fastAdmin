<?php

namespace models\common\db;

//use \console\CConsole;
//use \models\ConsoleError;

use models\common\sys\Sys;

class MysqlCnn
{
    /**
     * @var null|\PDO
     */
    public  $pdo         = null;
    public  $commandText = '';
    public  $cmd         = null;
    public  $prefix      = '';
    public  $bindData    = [];
    public  $readOnly    = false;
    public  $cts         = '';
    private $config      = [];
    public  $dbname      = '';

    /**
     * @param $config
     * @return MysqlCnn
     * @throws
     */
    public static function configDb($config)
    {
        try
        {
            return new MysqlCnn($config);
        } catch (\Exception $e)
        {
            Sys::app()->addLog([$config], '数据库配置');
            Sys::app()->interruption()->setMsg('操作失败' . '数据库链接失败' . $e->getMessage() . '[' . $e->getCode() . ']')->setDebugData($config)->outError();
        }
    }

    public function __construct($config)
    {
        $this->prefix   = isset($config['prefix']) ? trim($config['prefix']) : '';
        $this->readOnly = isset($config['readOnly']) ? $config['readOnly'] : false;
        $this->cts      = date('Y-m-d H:i:s', time());
        $this->config   = $config;
        $this->dbname   = $config['dbname'];

        $this->reconnect();
    }


    /**
     * @return MysqlCnn
     * @throws \Exception
     */
    public function reconnect()
    {
        $opt = array(
            // \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}",
            \PDO::ATTR_TIMEOUT          => 10,
            \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT       => true,
            \PDO::ATTR_EMULATE_PREPARES => true
        );
        try
        {
            $this->pdo = new \PDO("mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']};charset={$this->config['charset']}", $this->config['username'], $this->config['password'], array_merge($this->config['attributes'], $opt));
        } catch (\Exception $e)
        {
            Sys::app()->addLog([$this->config, $opt], '数据库配置');
            Sys::app()->interruption()->setMsg('操作失败' . '数据库链接失败' . $e->getMessage() . $e->getCode())->setDebugData($this->config)->outError();
        }
        return $this;

    }

    public function getPdo()
    {
        return $this->pdo;
    }


    public function __get($attr)
    {
        if (isset($this->$attr))
            return $this->$attr;
        throw new \Exception('could find attr');
    }


    /**
     * @param $commandText
     * @return MysqlCnnCmd
     * @throws
     */
    public function setText($commandText)
    {
        if ($this->readOnly && (strstr($commandText, 'insert ') || strstr($commandText, 'update ')))
            Sys::app()->interruption()->setMsg('操作失败')->setCode('mysql_error_readonly')->outError();
        $commandText = $this->prefix ? preg_replace('/{(.*?)}/', $this->prefix . '_$1', $commandText) : $commandText;

        return new MysqlCnnCmd($this, $commandText);
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
        $this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->beginTransaction();
    }

}


class MysqlCnnCmd
{
    private $commandText = '';
    /**
     * @var MysqlCnn
     */
    private $cnn      = null;
    private $bindData = [];
    /**
     * @var null|\PDOStatement
     */
    private $cmd = null;

    private $retry_max_times = 0;
    private $retry_wait_secs = 1;
    private $__retried_times = 0;


    public function __construct(MysqlCnn $cnn, $commandText)
    {
        $this->cnn         = $cnn;
        $this->commandText = $commandText;
        $this->newPDOStatement();
    }

    /**
     * 设置超时重试，注意 是【重试】次数，不是最多执行多少次，如果设置为1，那最多执行两次
     * @param $max_times
     * @param $wait_seconds
     * @return $this
     */
    public function setRetry($max_times, $wait_seconds)
    {
        $this->retry_max_times = $max_times;
        $this->retry_wait_secs = $wait_seconds;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function newPDOStatement()
    {
        try
        {
            $this->cnn->reconnect();
            $this->cmd = $this->cnn->pdo->prepare($this->commandText);
        } catch (\PDOException $e)
        {
            Sys::app()->interruption()->setMsg('操作失败')->setCode('mysql_error_exec_error')->setDebugMsg($e->getMessage())->setDebugData([
                $this->commandText,
                $this->bindData
            ])->outError();
        }
        return $this;
    }


    public function getText()
    {
        return $this->commandText;
    }

    public function bind($bindKey, $param)
    {
        $this->bindData[$bindKey] = $param;
        $this->cmd->bindValue($bindKey, $param);
        return $this;
    }

    public function bindArray($array)
    {
        try
        {
            $this->bindData = [];
            foreach ($array as $bindKey => $bindValue)
            {
                $this->bindData[$bindKey] = $bindValue;
                $this->cmd->bindValue($bindKey, $bindValue);
            }
        } catch (\PDOException $e)
        {
            Sys::app()->interruption()->setMsg('操作失败')->setCode('mysql_error_exec_error')->setDebugMsg($e->getMessage())->setDebugData([
                $this->commandText,
                $this->bindData
            ])->outError();
        }

        return $this;
    }

    private function __execute()
    {
        Sys::app()->addLog(['sql' => $this->commandText, 'bind' => $this->bindData]);

        try
        {
            $this->cmd->execute();
            return $this->cmd->rowCount();
        } catch (\PDOException $e)
        {
            $err_msg = $e->getMessage();

            if (strstr(strpos($err_msg, 'server has gone away') !== false) && $this->__retried_times < $this->retry_max_times)
            {
                sleep($this->retry_wait_secs);
                $this->__retried_times += 1;
                return $this->newPDOStatement()->bindArray($this->bindData)->__execute();
            }
            preg_match_all('/:\w+/', $this->commandText, $ar);
            $bindKeys = array_keys($this->bindData);
            Sys::app()->interruption()->setMsg('操作失败' . $e->getMessage())->setCode('mysql_error_exec_error')->setDebugMsg($e->getMessage())->setDebugData([
                'sql'  => $this->commandText,
                'bind' => $this->bindData,
                ['bindMore' => array_diff($bindKeys, $ar[0]), 'sqlMore' => array_diff($ar[0], $bindKeys)]
            ])->outError();
        }
    }


    public function execute()
    {
        return $this->__execute();
    }

    public function queryAll()
    {
        $this->__execute();
        return $this->cmd->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function queryRow()
    {
        $this->__execute();
        if (!$this->cmd->rowCount())
            return false;
        $result = array();
        try
        {
            while ($row = $this->cmd->fetch(\PDO::FETCH_ASSOC))
            {
                $result = $row;
                break;
            }
        } catch (\PDOException $e)
        {
            Sys::app()->interruption()->setMsg('读取失败')->setCode('mysql_error_exec_error')->setDebugMsg($e->getMessage())->setDebugData([
                $this->commandText,
                $this->bindData
            ])->outError();
        }

        return $result;
    }

    public function queryScalar()
    {
        $this->__execute();
        if (!$this->cmd->rowCount())
            return false;
        $this->cmd->setFetchMode(\PDO::FETCH_BOTH);
        $result = array();
        while ($row = $this->cmd->fetch())
        {
            $result = $row;
            break;
        }
        return $result[0];
    }

    public function lastInsertId()
    {
        return $this->cnn->pdo->lastInsertId();
    }
}




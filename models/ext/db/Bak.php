<?php

namespace models\ext\db;

use models\common\db\MysqlPdo;
use models\common\sys\Sys;

class Bak
{
    private $db;
    private $store_table_name;
    private $store_row_id = 0;

    private $src_table_name    = '';
    private $src_table_pk_key  = '';
    private $curr_table_pk_val = 0;


    public function __construct(MysqlPdo $db, $table)
    {
        $this->db               = $db;
        $this->store_table_name = $table;
    }

    /**
     * @return MysqlPdo
     */
    public function getDbConnent()
    {
        return $this->db;
    }

    /**
     * @return static
     */
    public function clearPk()
    {
        $this->curr_table_pk_val = 0;
        return $this;
    }

    /**
     * @param $src_table_name
     * @param $src_table_pk_key
     * @return static
     */
    public function setSrcInfo($src_table_name, $src_table_pk_key)
    {
        $this->src_table_name   = $src_table_name;
        $this->src_table_pk_key = $src_table_pk_key;
        return $this;
    }

    /**
     * @param $pk
     * @param $flag
     * @return bool
     * @throws \Exception
     */
    public function bakRowByPk($pk, $flag)
    {
        return $this->bakRow($this->db->setText("select * from {$this->src_table_name} where `{$this->src_table_pk_key}`=:pk")->bindArray([':pk' => $pk])->queryRow(), $flag);
    }

    /**
     * @param $data
     * @param $flag
     * @return bool
     * @throws \Exception
     */
    public function bakRow($data, $flag)
    {
        if (empty($data))
        {
            throw new \Exception('空数据');
        }
        if (!empty($this->curr_table_pk_val))
        {
            throw new \Exception('pk 没清掉之前，不能重新调');
        }
        if (!empty($this->store_row_id))
        {
            throw new \Exception('记录 没清掉之前，不能重新调');
        }
        $this->curr_table_pk_val = $data[$this->src_table_pk_key];
        $res                     = $this->db->setText("insert into {$this->store_table_name} set src_table_name=:tn,src_pk=:pk,row_json=:json,op_flag=:op_flag")->bindArray([
            ':tn'      => $this->src_table_name,
            ':pk'      => $this->curr_table_pk_val,
            ':json'    => json_encode($data),
            ':op_flag' => $flag
        ])->execute();

        $this->store_row_id = $this->db->lastInsertId();
        if ($res && $this->store_row_id)
        {
            return true;
        }
        return false;
    }

    public function tryDel()
    {
        if (empty($this->curr_table_pk_val) || empty($this->store_row_id))
        {
            throw new \Exception('pk 没保存成功，不能下一步');
        }
        $this->db->setText("delete from {$this->src_table_name} where `{$this->src_table_pk_key}`=:val")->bindArray([':val' => $this->curr_table_pk_val])->execute();

        $this->db->setText("update {$this->store_table_name} set is_del=1 where id={$this->store_row_id}")->execute();
        $this->store_row_id      = 0;
        $this->curr_table_pk_val = 0;


    }

    public function getDataRowsByFlag($flag)
    {
        return $this->db->setText("select * from  {$this->store_table_name}  where op_flag=:flag")->bindArray([':flag' => $flag])->queryAll();
    }

    public function rollBackRow($row)
    {
        $table_name = $row['src_table_name'];
        $row_data   = json_decode($row['row_json'], true);
        if (empty($row_data))
        {
            echo 'ERROR_DATA';
            var_dump($row_data);
            return false;
        }
        $binds = [];
        $sets  = [];
        foreach ($row_data as $k => $v)
        {
            if (!is_numeric($k))
            {
                $sets[]         = "`{$k}`=:{$k}";
                $binds[":{$k}"] = $v;
            }
        }
        $set_str = join(',', $sets);
        $sql     = "insert ignore into {$table_name} set {$set_str}";
        $res     = $this->db->setText($sql)->bindArray($binds)->execute();
        $status  = $this->db->setText("update {$this->store_table_name} set is_rollbak=1 where id={$row_data['id']}")->execute();
        echo "\n";
        echo json_encode([
            'bak_info'   => $row_data,
            'table'      => $table_name,
            'sql'        => $sql,
            'bind'       => $binds,
            'insert'     => $res,
            'bak_status' => $status,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n";

    }


}


<?php

namespace models\ext\tool;

use models\common\sys\Sys;

//session_start();
class File
{
    private $fp;
    private $lines_number = 0;
    private $_inited      = [
        'lines_number' => false,
    ];

    public function __construct($filename)
    {
        $this->fp = fopen($filename, 'r');
    }

    public function __destruct()
    {
        //echo "\n __destruct \n";
        fclose($this->fp);
    }

    public function countLineNumber()
    {
        if ($this->_inited['lines_number'] === false)
        {
            while (!feof($this->fp))
            {
                fgets($this->fp);
                //$line = trim(fgets($this->fp));
                $this->lines_number += 1;
            }
            // var_dump($this->lines_number);
            $this->_inited['lines_number'] = true;
        }

    }

    public function getLinesNumber()
    {
        $this->countLineNumber();
        return $this->lines_number;
    }

    public function getHeaderAndTailStrs()
    {
        $this->countLineNumber();
        $strs = [];
        $i    = 0;
        $last = $this->lines_number - 3;// 数组index -1 ,最后以为是空行再 -1 ,因为使用的是> 不含等于，再-1
        fseek($this->fp, 0);
        while (!feof($this->fp))
        {
            $str = trim(fgets($this->fp));
            //$line = trim(fgets($this->fp));

            if ($str)
            {
                if ($i < 1 || $i > $last)
                {
                    $strs[] = $str;
                }
            }

            $i++;
        }
        return $strs;

    }


    public function getStrs($offset, $length)
    {
        $this->countLineNumber();
        if ($offset < 0)
        {
            $offset = $this->lines_number + $offset;
        }
        if ($offset >= $this->lines_number)
        {
            throw new \Exception("起始位置超出文件最后一行了 {$offset}>{$this->lines_number}");
        }
        if ($offset < 0)
        {
            throw new \Exception("实际起始位置负数 {$offset} <0 ,总行数:{$this->lines_number} ");
        }
        $strs        = [];
        $true_offset = 0;
        $last_offset = 0;
        if ($length > 0)
        {
            $true_offset = $offset;
            $last_offset = $true_offset + $length;
            // $length      = $last_offset > $this->lines_number ? ($length - ($last_offset - $this->lines_number)) : $length;
            $last_offset = $last_offset > $this->lines_number ? $this->lines_number : $last_offset;

        }
        else
        {
            $true_offset = $offset + $length;
            if ($true_offset < 0)
            {
                $true_offset = 0;
                $last_offset = -$length;
            }
            else
            {
                $last_offset = $offset;
            }
        }

        var_dump([$true_offset, $last_offset]);
        for ($tmp_i = $true_offset; $tmp_i < $last_offset; $tmp_i++)
        {
            echo "{$tmp_i}\n";
            if (fseek($this->fp, $tmp_i, SEEK_CUR) === 0)
            {
                // var_dump(fgets($this->fp));
                //$strs[] = fread($this->fp,1);
                $strs[] = fgets($this->fp, 100000);

            }
            else
            {
                throw new \Exception("fseek [{$tmp_i}] 行 失败");
            }
        }
        return $strs;

    }


    /**
     * 取文件最后$n行
     * @param string $filename 文件路径
     * @param int $n 最后几行
     * @return mixed false表示有错误，成功则返回字符串
     */
    function FileLastLines($filename, $n)
    {
        if (!$fp = fopen($filename, 'r'))
        {
            echo "打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文";
            return false;
        }
        $pos = -2;
        $eof = "";
        $str = "";
        while ($n > 0)
        {
            while ($eof != "\n")
            {
                if (!fseek($fp, $pos, SEEK_END))
                {
                    $eof = fgetc($fp);
                    $pos--;
                }
                else
                {
                    break;
                }
            }
            $str .= fgets($fp);
            $eof = "";
            $n--;
        }
        return $str;
    }

}
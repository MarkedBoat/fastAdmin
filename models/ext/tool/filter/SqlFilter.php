<?php

namespace models\ext\tool\filter;

class SqlFilter
{
    /**
     * @var array
     *      -skip  true|false
     *      range array();
     */
    private $opts      = array();
    private $flag      = true;//过滤器返回的标识，如果是true，那么过滤结果为true时采用数据，其它结果抛弃
    private $fun       = null;
    private $filter    = 'opt';   //过滤者  opt:选项 setSql|setOpt  caller:匿名函数 设置的时候 匿名函数优先
    private $bind_data = array();


    const flag_save   = true; //通过条件的保留
    const flag_ignore = false;//通过条件的跳过


    const opt_and      = 'and';
    const opt_or       = 'or';
    const opt_in       = 'in';
    const opt_not_in   = 'notin';
    const opt_like     = 'like';
    const opt_eq       = '=';  // equal 等于
    const opt_nq       = '!='; //not equal 不等于
    const opt_gt       = '>';  //getter than 大于
    const opt_lt       = '<';  //less than 小于
    const opt_ge       = '>='; // Getter than or Equal 大于等于
    const opt_le       = '<='; //Less than or Equal 小于等于
    const opt_is_null  = 'is_null';
    const opt_not_null = 'not_null';


    static public function model()
    {
        return new static();
    }


    /**
     * 设置过滤函数，return true 保留结果，false|void 过滤掉
     * @param callable $fun
     * @param bool $flag
     * @return static
     * @throws \Exception
     */
    public function setFun($fun, $flag = true)
    {
        if (is_callable($fun))
        {
            $this->fun = $fun;
        }
        else
        {
            throw  new \Exception(400, '资源不存在');
        }
        $this->flag = $flag;
        return $this;
    }


    /**
     * 设置sql 语句，过滤，关于
     * - 不得出现 函数 、隐式转换取反 : isnull|!
     * - 计算 null 判断 只能用  xxx is not null | xx is null
     * - 支持语法  and,or,>=,<=,!=,>,<,=,in,like,is not null,is null
     * @param string $str
     * @param bool $flag
     * @return  static
     * @throws \Exception
     */
    public function setBySql($str, $flag = true)
    {
        //Common_Sysadv::app()->echoVar($str);
        //先去除空格之类的
        $str = str_replace(array('(', ')'), array(' ( ', ' ) '), $str);
        $str = trim(preg_replace('/\s+/', ' ', $str));
        $str = str_replace(array(' is not null', ' is null'), array('={not_null}', '={is_null}'), $str);
        $ar  = explode(' ', $str);
        //Common_Sysadv::app()->echoVar($str);
        $this->opts=[];
        $this->opts   = $this->initRelation($ar);
        $this->flag   = $flag;
        $this->filter = 'opt';
        return $this;
    }

    public function initRelation($array)
    {
        //每一层只能出现同样的 and 或 or，不能同时出现
        $signs        = array();
        $kws_lev1     = array('and', 'or');
        $lefts        = array();
        $rights       = array();
        $opts         = array(0 => array());
        $lev1_index   = 0;
        $deep         = 0;
        $is_middle_kw = 0;
        $kws_middle   = array('in', 'like');
        //  var_dump($array);
        foreach ($array as $ele)
        {
            if (in_array($ele, $kws_lev1))
            {
                if ($deep === 0)
                {
                    $lev1_index++;
                    $opts[$lev1_index] = array();
                    $signs[]           = $ele;
                }
                else
                {
                    $opts[$lev1_index][] = $ele;
                }
            }
            else if ($ele === '(')
            {
                $lefts[] = $ele;
                if ($deep > 0)
                {
                    $opts[$lev1_index][] = $ele;
                }
                $deep++;
            }
            else if ($ele === ')')
            {
                $rights[] = $ele;
                $deep--;
                if ($deep > 0)
                {
                    $opts[$lev1_index][] = $ele;
                }
            }
            else
            {
                //  4=>c
                //  5=>in
                //  6=>(
                //  7=>3,4
                //  8=>)
                //  所谓的 $kws_middle  就是  关键词在中间的  in ,like 等，需要把kw 放在首位，以方便判断就是 比如判定 'in '就不需要判断 ' in '，万一原sql 是  c not in ( ' in ' )，这样就判断错了
                // 上面的结果就会被转化成 in c 3,4  对应的原sql :  c in (3,4)  其中的括号部分被抵消了，不会造成影响
                $lev2_index = count($opts[$lev1_index]) - 1;
                if (in_array($ele, $kws_middle))
                {
                    //这个时候的 $ele 是 in ,但是原值是  c  ，最后变成 in c
                    $opts[$lev1_index][$lev2_index] = $ele . ' ' . $opts[$lev1_index][$lev2_index];
                    $is_middle_kw                   = 2;
                }
                else
                {
                    if ($is_middle_kw === 2)
                    {
                        $opts[$lev1_index][$lev2_index] = $opts[$lev1_index][$lev2_index] . ' ' . $ele;
                        $is_middle_kw                   = 0;
                    }
                    else
                    {
                        $opts[$lev1_index][] = $ele;
                    }
                }
            }
            //var_dump([$ele,$deep,$opts]);
        }
        if (count($lefts) !== count($rights))
        {
            throw  new \Exception(400, 'SQL有问题 括号不对称');
        }
        $signs = array_unique($signs);
        $cnt   = count($signs);
        if ($cnt > 1)
        {
            throw  new \Exception(400, 'SQL有问题 and or 出现在了同一层');
        }
        else
        {
            //var_export($opts);
            //[
            //  0 =>  [   0 => 'a>0', ],
            //  1 =>  [   0 => 'b=2',],
            //  2 =>  [   0 => 'in c 3,4',],
            //  3 =>  [   0 => 'd!=4', ],
            //]

            // Common_Sysadv::app()->echoVar($signs, '$signs');
            // Common_Sysadv::app()->echoVar($opts, '$opts');
            foreach ($opts as $i => $opt)
            {
                if (count($opt) === 1 && is_string($opt[0]))
                {
                    $opts[$i] = $this->opt($opt[0]);
                }
                else
                {
                    $opts[$i] = $this->initRelation($opt);
                }
            }
            //   var_dump($opts);
            return array($cnt ? $signs[0] : 'and', $opts);
        }
    }

    public function opt($str)
    {

        $kws      = explode(',', '>=,<=,<>,!=,=,>,<');
        $kws3     = explode(',', 'in,like');
        $kws2_map = array(
            '{is_null}'  => self::opt_is_null,
            '{not_null}' => self::opt_not_null,
        );

        $array = array();
        foreach ($kws as $kw)
        {
            if (strstr($str, $kw))
            {

                $row = explode($kw, $str);

                if (count($row) === 2)
                {
                    if ($row[1][0] === '{' && isset($kws2_map[$row[1]]))
                    {
                        $array = array($kws2_map[$row[1]], $row[0], '');
                    }
                    else
                    {
                        $array = array($kw, $row[0], $row[1]);
                    }
                    break;
                }
            }
        }
        foreach ($kws3 as $kw)
        {
            if (strstr($str, $kw . ' '))
            {
                $row = explode(' ', $str);
                if (count($row) === 3)
                {
                    $array = $row;
                    break;
                }
            }
        }
        return $array;
    }


    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param $row
     * @return bool  true:通过  false:被过滤掉   !!! 注意这是和预留标记对比过的
     * @throws \Exception
     */
    public function isSave($row)
    {
        $flag = true;

        if (count($this->opts))
        {
            //    var_dump($this->opts);
            $flag = $this->filter($row, $this->opts);
            //  var_dump([1,$flag]);
        }
        //上面的flag 只是过滤器的结果，看预留的flag（即$this->flag）要的是什么值？  如果一致保留，不一致的忽略
        return $flag === $this->flag;
    }

    public function filter($row, $opt)
    {
        //   var_dump($opt);
        $result = false;
        if ($opt[0] === 'and')
        {
            foreach ($opt[1] as $opt_sub)
            {
                if ($this->filter($row, $opt_sub))
                {
                    $result = true;
                }
                else
                {
                    $result = false;
                    break;
                }
            }
        }
        else if ($opt[0] === 'or')
        {
            foreach ($opt[1] as $opt_sub)
            {
                if ($this->filter($row, $opt_sub))
                {
                    $result = true;
                    break;
                }
            }
        }
        else if (count($opt) === 3)
        {
            $val   = $opt[2];
            $field = $opt[1];
            if (isset($val[0]) && $val[0] === ':' && isset($this->bind_data[$val]))
            {
                $val = $this->bind_data[$val];
            }
            switch ($opt[0])
            {
                case  self::opt_ge:
                    $result = $row[$field] >= $val;
                    break;
                case  self::opt_le:
                    $result = $row[$field] <= $val;
                    break;
                case  self::opt_gt:
                    $result = $row[$field] > $val;
                    break;
                case  self::opt_lt:
                    $result = $row[$field] < $val;
                    break;
                case  self::opt_nq:
                    $result = $row[$field] !== $val;
                    break;
                case  self::opt_eq:
                    $result = $row[$field] === $val;
                    break;
                case  self::opt_in:
                    $ar     = array_map(function ($str)
                    {
                        $str2 = trim(trim($str, "'"), '"');
                        return $str2 === $str ? intval($str) : $str2;
                    }, explode(',', $val));
                    $result = in_array($row[$field], $ar, true);
                    break;
                case  self::opt_not_in:
                    $ar     = array_map(function ($str)
                    {
                        $str2 = trim(trim($str, "'"), '"');
                        return $str2 === $str ? intval($str) : $str2;
                    }, explode(',', $val));
                    $result = !in_array($row[$field], $ar, true);
                    break;
                case  self::opt_like:
                    $result = strstr($row[$field], $val) ? true : false;
                    break;
                case  self::opt_is_null:
                    $result = is_null($row[$field]);
                    break;
                case  self::opt_not_null:
                    $result = !is_null($row[$field]);
                    break;
            };
            $yn = $result ? 'yes' : 'no';
            // var_dump($val, $row[$field]);
            //  echo "filed:{$field} {$opt[0]} {$val} input:{$row[$field]} res:{$yn}\n";
        }
        else
        {
            throw  new \Exception(400, '未知的过滤选项' . var_export($opt));
        }
        //  var_dump([2,$result]);
        //   debug_print_backtrace(0,4);
        return $result;
    }


    public function getInfo()
    {
        return array('opts' => $this->opts, 'flag' => $this->flag, 'bind_data' => $this->bind_data,);
    }

}
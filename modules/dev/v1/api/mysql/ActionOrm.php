<?php

namespace modules\dev\v1\api\mysql;

use models\common\ActionBase;
use models\common\sys\Sys;


class ActionOrm extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public function __construct($param = [])
    {
        parent::init($param);
    }

    public function run()
    {

        $conf = $this->inputDataBox->getStringNotNull('conf');
        $tn   = $this->inputDataBox->getStringNotNull('table');
        //$db    = $this->params->getStringNotNull('db');


        // $table = 'sl_client';
        $sql   = "show full columns from {$tn};";
        $table = Sys::app()->db($conf)->setText($sql)->queryAll();

        echo '<pre>';
        $attrs       = [];
        $set0        = [];
        $set1        = [];
        $pri_key_map = [];
        $pri_key     = '';
        $map         = [];
        $pro_map     = [];
        $str_map     = ['public static $field_config=['];
        foreach ($table as $row)
        {
            preg_match('/(\w+)(\(\d+\))?/i', $row['Type'], $ar);
            $tmp_cnt = count($ar);
            if ($tmp_cnt < 2 && $tmp_cnt > 3)
            {
                var_dump($ar);
                die('xxxxxxxxxxxxxxxxxxx');
            }
            $map[$row['Field']] = [
                'db_type' => $ar[1],
                'length'  => isset($ar[2]) ? str_replace(['(', ')'], '', $ar[2]) : 0,
            ];
            //$str_map[]          = "'{$row['Field']}'=>['db_type'=>'{$ar[1]}','length'=>{$map[$row['Field']]['length']},'comment'=>'{$row['Comment']}'],";

            $def  = is_null($row['Default']) ? 'null' : (strstr($row['Type'], 'int') ? intval($row['Default']) : "'{$row['Default']}'");
            $def2 = is_null($row['Default']) ? 'null' : (strstr($row['Type'], 'int') ? intval($row['Default']) : (strstr($row['Type'], 'char') ? "'{$row['Default']}'" : "null"));

            $type      = strstr($row['Type'], 'int') ? 'int' : 'string';
            $pro_map[] = "public \${$row['Field']}={$def2};";
            $str_map[] = "'{$row['Field']}'=>['db_type'=>'{$ar[1]}','length'=>{$map[$row['Field']]['length']},'def'=>$def,'pro_def'=>$def2],";

            echo "* @property {$type} {$row['Field']} {$row['Comment']}\n";
            $attrs[]             = "'{$row['Field']}'";
            $bind_key            = ":{$row['Field']}";
            $set0[$row['Field']] = "`{$row['Field']}`={$bind_key}";
            if ($row['Key'] === 'PRI')
            {
                $pri_key                    = $row['Field'];
                $pri_key_map[$row['Field']] = "`{$row['Field']}`={$bind_key}";
            }
            else
            {
                $set1[$row['Field']] = "`{$row['Field']}`={$bind_key}";
            }
        }
        $str_map[] = '];';
        echo "\n";
        $str  = join(',', $attrs);
        $str2 = str_replace("'", '`', $str);
        echo " const fields = '{$str2}';\n\n";


        echo "\n" . join("\n", $pro_map) . "\n\n\n\n";
        echo "public static \$_fields_str;\n";
        echo "public static \$tableName='{$tn}';\n";
        echo "public static \$pk='{$pri_key}';\n";
        echo join("\n", $str_map);


        echo "\n\n\n";
        foreach ($table as $row)
        {
            $type = strstr($row['Type'], 'int') ? 'int' : 'string';
            echo "* @property {$type} _{$row['Field']} {$row['Comment']}\n";
        }
        echo "\n\n\n";
        foreach ($table as $row)
        {
            // echo " public \${$row['Field']}; //{$row['Comment']}\n";
        }
        //  echo " protected \$allAttrKeys = [{$str}];\n";

        ksort($set0);
        ksort($set1);
        ksort($pri_key_map);
        $keys        = array_keys($set0);
        $set0        = join(',', $set0);
        $set1        = join(',', $set1);
        $pri_key_map = join(' and ', $pri_key_map);

        $sql_insert1 = "insert ignore into {$tn} set {$set0}";
        $sql_insert2 = "insert ignore into {$tn} set {$set0} on duplicate key update {$set1}";
        $sql_update  = "update {$tn} set {$set1} where {$pri_key_map}";

        echo "\n{$sql_insert1}\n{$sql_insert2}\n{$sql_update}\n\n";
        var_dump($keys);
        echo "\n";
        die('</pre>');

    }

}
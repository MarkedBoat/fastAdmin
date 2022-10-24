<?php
/**
 *
 */

namespace console\bee_invasion\dbdata;

use models\common\CmdBase;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use modules\bee_invasion\v1\model\admin\dbdata\DbStruct;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\play\Equipment;

class CmdIndex extends CmdBase
{


    public static function getClassName()
    {
        return __CLASS__;
    }


    public function exportStructToJsFile()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 清理缓存  start\n";

        $daos  = DbStruct::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]);
        $datas = [];
        foreach ($daos as $dao)
        {
            $datas[] = $dao->getOpenInfo();
        }

        $json     = json_encode($datas);
        $filename = __ROOT_DIR__ . '/static/file/admin/js/db_struct.js';
        $res      = file_put_contents($filename, "\nwindow.db_struct={$json};\n");
        var_dump($res, $filename);

    }


    public function exportBiItemToJsFile()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 清理缓存  start\n";

        $daoss = [
            'currency'  => Currency::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'cg'        => ConsumableGoods::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'object'    => MObject::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
            'equipment' => Equipment::model()->setLimit(0, 1000)->findAllByWhere(['is_ok' => Opt::YES]),
        ];
        $datas = [];
        foreach ($daoss as $class => $daos)
        {
            /** @var  $dao  Currency|ConsumableGoods|MObject | Equipment */
            foreach ($daos as $dao)
            {
                $info               = $dao->getOpenInfo();
                $info['item_flag']  = $class . '/' . $dao->item_code;
                $info['class_code'] = $class;
                $datas[]            = $info;
            }
        }

        $json     = json_encode($datas);
        $filename = __ROOT_DIR__ . '/static/file/admin/js/bi_items.js';
        $res      = file_put_contents($filename, "\nwindow.bi_items={$json};\n");
        var_dump($res, $filename);

    }


}
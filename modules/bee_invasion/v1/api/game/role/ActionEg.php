<?php

namespace modules\bee_invasion\v1\api\game\role;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\game\Channel;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\play\Perk;


class ActionEg extends GameBaseAction
{
    public static function getClassName()
    {
        return __CLASS__;
    }


    public function run()
    {
        if (0)
        {
            $dao = RoleDao::model()->findByPk(3);
            return $dao->getSetData();

        }
        if (0)
        {
            $dao = RoleDao::model()->findOneByWhere(['user_id' => 2]);
            return ['eg' => $dao->getSetData(), 'yyyyy' => 'xxxxxx'];

        }
        if (0)
        {
            return [
                'eg' => [
                    'row'    => Sys::app()->db('dev')->setText("select * from bi_user where id=:id")->bindArray([':id' => 1])->queryRow(),
                    'all'    => Sys::app()->db('dev')->setText("select * from bi_user where id=:id")->bindArray([':id' => 1])->queryAll(),
                    'scalar' => Sys::app()->db('dev')->setText("select nickname from bi_user where id=:id")->bindArray([':id' => 1])->queryScalar()

                ]
            ];

        }


        if (0)
        {
            throw new AdvError(AdvError::user_error, '演示');

        }

        if (0)
        {
            return $this->dispatcher->createInterruption('eg_erorr11', '错误演示', ['返回的数据'], ['调试数据']);

        }

        if (1)
        {
            return [
                ConsumableGoods::model()->getCachedItemCodes(),
                'cg_list'        => array_values((new ConsumableGoods())->getItemInfos(true)),
                'channel_list'   => array_values((new Channel())->getItemInfos(true)),
                'config_list'    => array_values((new Config())->getItemInfos(true)),
                'currency_list'  => array_values((new Currency())->getItemInfos(true)),
                'equipment_list' => array_values((new Equipment())->getItemInfos(true)),
                'perk_list'      => array_values((new Perk())->getItemInfos(true)),
            ];

        }


    }
}
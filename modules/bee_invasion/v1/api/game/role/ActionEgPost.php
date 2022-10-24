<?php

namespace modules\bee_invasion\v1\api\game\role;

use models\common\error\AdvError;
use models\common\sys\Sys;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\RoleDao;


class ActionEgPost extends GameBaseAction
{
    public $requestMethods = ['POST'];

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

        if (1)
        {
            return $this->dispatcher->createInterruption('eg_erorr11', '错误演示', ['返回的数据'], ['调试数据']);

        }

    }
}

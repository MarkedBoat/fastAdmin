<?php

namespace models\common\data;


use models\common\db\DbModel;

class MetalData
{
    protected $orm;

    public function __construct(DbModel $model)
    {
        $this->orm = $model;
    }


    /**
     * @param DbModel $model
     * @return static
     */
    public static function model(DbModel $model)
    {
        return new static($model);
    }

    /**
     * @return DbModel
     */
    public function getOrm()
    {
        return $this->orm;
    }

    public function findByPk($pk)
    {
        return $this->orm->findByPk($pk);
    }


}


<?php

namespace modules\_dp\v1\model\dbdata;


use modules\_dp\v1\dao\dbdata\DbStructDao;
use modules\_dp\v1\model\TInfo;

class DbStruct extends DbStructDao
{


    public function getOpenInfo()
    {
        return [
            'title'       => $this->title,
            'struct_code' => $this->struct_code,
            'remark'      => $this->remark,
            'struct_json' => $this->getJsondecodedValue($this->struct_json, 'array'),
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];


    }
}
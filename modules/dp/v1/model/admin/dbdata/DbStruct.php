<?php

namespace modules\dp\v1\model\admin\dbdata;


use modules\dp\v1\dao\admin\dbdata\DbStructDao;
use modules\dp\v1\model\TInfo;

class DbStruct extends DbStructDao
{
    use TInfo;

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
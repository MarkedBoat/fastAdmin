<?php

namespace modules\_dp\v1\model\dbdata;


use models\common\db\MysqlPdo;
use models\common\opt\Opt;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbColumnDao;
use modules\_dp\v1\dao\dbdata\DbDbConfDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;

class DbDbConf extends DbDbConfDao
{


    public function getOpenInfo()
    {
        return [
            'title'       => $this->title,
            'remark'      => $this->remark,
            'read_roles'  => $this->getJsondecodedValue($this->read_roles, 'array'),
            'all_roles'   => $this->getJsondecodedValue($this->all_roles, 'array'),
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];


    }

    public function getConfDbConnect()
    {
        $dev_cfg = [
            'connectionString' => "mysql:host={$this->conf_host};port={$this->conf_port};dbname={$this->conf_dbname}",
            'username'         => $this->conf_username,
            'password'         => $this->conf_password,
            'charset'          => $this->conf_charset,
            'readOnly'         => true,
            'attributes'       => [
                \PDO::ATTR_TIMEOUT => 1
            ]
        ];
        return MysqlPdo::configDb($dev_cfg);

    }
}
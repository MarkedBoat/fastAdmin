<?php

namespace modules\_dp\v1\model\dbdata;


use http\Client\Curl\User;
use models\common\db\MysqlPdo;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbColumnDao;
use modules\_dp\v1\dao\dbdata\DbDbConfDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;
use modules\_dp\v1\model\Admin;

class DbDbConf extends DbDbConfDao
{
    private static $connecetions = [];

    public function getOpenInfo()
    {
        return [
            'title'       => $this->title,
            'remark'      => $this->remark,
            'accessRoles' => $this->getJsondecodedValue($this->access_role_codes, 'array'),
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];


    }

    public function getConfDbConnect()
    {
        if ($this->db_code === '_sys_')
        {
            return $this->getDbConnect();
        }
        if (!isset(self::$connecetions[$this->db_code]))
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
            //Sys::app()->addLog($dev_cfg);
            self::$connecetions[$this->db_code] = MysqlPdo::configDb($dev_cfg);
        }

        return self::$connecetions[$this->db_code];

    }


    public function checkAccess(Admin $user)
    {
        $user_roles   = $user->role_codes;
        $access_field = 'access_role_codes';
        if (is_null($this->$access_field))
        {
            Sys::app()->addLog("dbconf_check_access_{$access_field} is null");
            return false;
        }
        $access_roles = $this->getJsondecodedValue($this->$access_field, 'array');
        if (count($access_roles) === 0)
        {
            Sys::app()->addLog([$access_roles, $this->$access_field], "dbconf_check_access_{$access_field} empty array");
            return false;
        }
        $intersect_roles = array_intersect($user_roles, $access_roles);
        if (count($intersect_roles) === 0)
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "dbconf_check_access_fail_{$access_field} array_intersect");
            return false;
        }
        else
        {
            Sys::app()->addLog([$user_roles, $access_roles, $intersect_roles], "dbconf_check_access_ok_{$access_field} array_intersect");
            return true;
        }
    }


}
<?php

namespace modules\_dp\v1\model\dbdata;


use models\common\opt\Opt;
use modules\_dp\v1\dao\AdminDao;
use modules\_dp\v1\dao\dbdata\DbRelationDao;
use modules\_dp\v1\dao\rbac\RbacActionDao;
use modules\_dp\v1\dao\game\RoleDao;
use modules\_dp\v1\dao\game\RoleLevCfgDao;
use modules\_dp\v1\dao\user\UserCgHisDao;
use modules\_dp\v1\dao\user\UserDao;
use modules\_dp\v1\dao\user\UserInviterDao;
use modules\_dp\v1\model\TCache;
use modules\_dp\v1\model\TInfo;

class DbRelation extends DbRelationDao
{
    const HAS_MANY = 'has_many';
    const HAS_ONE  = 'has_one';

    private $input_vals = [];

    /**
     * @param $vals
     * @return static
     */
    public function setInputVals($vals)
    {
        $this->input_vals = $vals;
        return $this;
    }

    public function getInputVals()
    {
        return $this->input_vals;
    }

    public function getAllInfo()
    {
        return [
            'id'                      => $this->id,
            'dbconf_code'             => $this->dbconf_name,
            'relation_table_name'     => $this->relation_table_name,
            'relation_left_field'     => $this->relation_left_field,
            'relation_right_field'    => $this->relation_right_field,
            'relation_ext_field'      => $this->relation_ext_field,
            'relation_ext_field_val'  => $this->relation_ext_field_val,
            'relation_res_key'        => $this->relation_res_key,
            'relation_res_title'      => $this->relation_res_title,
            'relation_type'           => $this->relation_type,
            'query_input_type'        => $this->query_input_type,
            'left_table_name'         => $this->left_table_name,
            'left_table_index_field'  => $this->left_table_index_field,
            'right_table_name'        => $this->right_table_name,
            'right_table_index_field' => $this->right_table_index_field,
            'right_table_label_field' => $this->right_table_label_field,
            'right_table_info_fields' => $this->right_table_info_fields,
            'is_right_as_filter'      => $this->is_right_as_filter,
            'filter_val_items_length' => $this->filter_val_items_length,
            'is_ok'                   => $this->is_ok,
            'val_items'               => DbDbConf::model()->findOneByWhere(['db_code' => $this->dbconf_name])->getConfDbConnect()->setText("select {$this->right_table_label_field} as text,{$this->right_table_index_field} as val from {$this->right_table_name} limit {$this->filter_val_items_length};")->queryAll()
        ];

    }
}
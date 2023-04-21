<?php

namespace modules\_dp\v1\dao\dbdata;

use models\common\db\DbModel;
use models\common\db\ORM;
use models\common\sys\Sys;

/**
 * @property int id
 * @property string dbconf_name 主表 db
 * @property string relation_table_name 关联表  表名
 * @property string realtion_left_field 关联表 left表值的字段名
 * @property string realtion_right_field 关联表 right表值的字段名
 * @property string relation_ext_field 关联表额外字段，用于综合索引表，所谓综合索引表 比如  db_op_log 就是类似，right 端为多个表，不是只有一个表，本质上应该设计成多个简单关联表，但愣是压成了一张表
 * @property string relation_ext_field_val 关联表额外字段 的值，用于索引表，至于为什么不把  relation_ext_field 和 relation_ext_field_val 合成一个 reation_ext_sql 只是为了防止后期忍不住破坏规矩
 * @property string realtion_res_key 关联结果 以 xx 为key
 * @property string realtion_type 关联关系 has_one ,has_many  不考虑 多对一 和 多对多
 * @property string left_table_name left  表名
 * @property string left_table_index_field left 表 字段名
 * @property string right_table_name
 * @property string right_table_index_field right表 字段名
 * @property string right_table_label_field 从right 表中 取出一个字段作为label, src_safe_columns取的信息会作为 info 附加上去(于label同级)
 * @property string right_table_info_fields 用于解释表中，有些关联字段，是敏感的，得去掉
 * @property int is_right_as_filter right是否作为 筛选项
 * @property int is_ok 是否正常  1:正常  2:被封禁
 * @property string create_time
 * @property string update_time
 */
class DbRelationDao extends ORM
{
    public $id=null;
    public $dbconf_name='';
    public $relation_table_name='';
    public $realtion_left_field='';
    public $realtion_right_field='';
    public $relation_ext_field='';
    public $relation_ext_field_val='';
    public $realtion_res_key='';
    public $realtion_type='';
    public $left_table_name='';
    public $left_table_index_field='';
    public $right_table_name='right 表名';
    public $right_table_index_field='';
    public $right_table_label_field='';
    public $right_table_info_fields='';
    public $is_right_as_filter=2;
    public $is_ok=1;
    public $create_time=null;
    public $update_time=null;



    public static $_fields_str;
    public static $tableName='bg_db_relation';
    public static $pk='id';
    public static $field_config=[
        'id'=>['db_type'=>'int','length'=>0,'def'=>null,'pro_def'=>null],
        'dbconf_name'=>['db_type'=>'varchar','length'=>16,'def'=>'','pro_def'=>''],
        'relation_table_name'=>['db_type'=>'varchar','length'=>64,'def'=>'','pro_def'=>''],
        'realtion_left_field'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'realtion_right_field'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'relation_ext_field'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'relation_ext_field_val'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'realtion_res_key'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'realtion_type'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'left_table_name'=>['db_type'=>'varchar','length'=>64,'def'=>'','pro_def'=>''],
        'left_table_index_field'=>['db_type'=>'varchar','length'=>32,'def'=>'','pro_def'=>''],
        'right_table_name'=>['db_type'=>'varchar','length'=>64,'def'=>'right 表名','pro_def'=>'right 表名'],
        'right_table_index_field'=>['db_type'=>'varchar','length'=>64,'def'=>'','pro_def'=>''],
        'right_table_label_field'=>['db_type'=>'varchar','length'=>64,'def'=>'','pro_def'=>''],
        'right_table_info_fields'=>['db_type'=>'varchar','length'=>512,'def'=>'','pro_def'=>''],
        'is_right_as_filter'=>['db_type'=>'tinyint','length'=>0,'def'=>2,'pro_def'=>2],
        'is_ok'=>['db_type'=>'tinyint','length'=>0,'def'=>1,'pro_def'=>1],
        'create_time'=>['db_type'=>'timestamp','length'=>0,'def'=>'CURRENT_TIMESTAMP','pro_def'=>null],
        'update_time'=>['db_type'=>'timestamp','length'=>0,'def'=>null,'pro_def'=>null],
    ];


    public function getDbConfName()
    {
        return '_sys_';
    }

}
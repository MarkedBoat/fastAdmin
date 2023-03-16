
#fastAdmin
针对短平快的项目背景下，数据运营后台成了脏活、累活，并且优先级不高，为运营部门提供一套即时可用的后台，也对开发人员更为友好，具备权限管理(RBAC)、菜单管理、跨数据库、字段更改跟踪等特征。

#####1.login
#####登录，直接输入对应的ip，会自动跳转这里
#####Login, directly input the corresponding IP, and it will automatically redirect here.
      

![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/1.login.png)

#####2.how to config a database connnetion setting;
##### 如何配置一个数据库连接
###### run sql
`CREATE SCHEMA `test4dev` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;`

`CREATE TABLE `test4dev`.`t1` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `str1` VARCHAR(45) NOT NULL DEFAULT '默认str1' COMMENT '字符串1',
   `str2` VARCHAR(255) NOT NULL DEFAULT 'string2' COMMENT '字符串2',
   `json1` JSON NULL COMMENT 'js 数据',
   `is_del` TINYINT(2) NOT NULL DEFAULT 2 COMMENT '是否删除  1:y 2:n',
   PRIMARY KEY (`id`));`

![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/2.how_to_add_a_db_config.png)

#####3 a new database connection config infomation;
##### 一个新数据库连接配置信息
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/3.a_new_db_config.png)

#####4 import tables from database connection config;
##### 导入对应的表
                                            

![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/4.import_db_config_tables_and_columns.png)

#####5 the new database connection config is ok,let's take a look at the situation of the data table.
##### 看下数据库连接下的表
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/5.goto_its_tables.png)
###6 Configure the fields of the specified table.
##### 配置指定表的字段
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/6.config_table_columns.png)
###7 click,drag,drop,sort
##### 见图
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/7.sort%20columns.png)
###8 sorted
##### 排序后的效果
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/8.sort%20columns%202.png)
###9 config a column
##### 配置一个字段
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/9.config_a_column.png)
###10 eg of column config, a 3 value options droplist for filed t1.is_del, string value of t1.str2 will be output as image.
##### 配置例子 t1.is_del 一个下拉列表，t1.str2 将会作为图片展示
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/10.config_a_column%202.png)
###11 goto table data rows page.
##### 去查看表数据
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/11.how_goto_table_rows_page.png)
####12 add a row for table.
##### 新增一条数据
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/12.how_add_a_data_row.png)
###13 a new data row
##### 一行新数据
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/13.set_a_new_row_data.png)

###14 display of new data row 
##### 数据展示效果
![image](https://raw.githubusercontent.com/MarkedBoat/fastAdmin/master/static/_doc/14.new_data_row_display.png)




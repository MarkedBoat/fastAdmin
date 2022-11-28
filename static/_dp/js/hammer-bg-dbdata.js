let hammerBgDataApi = function () {

    let apiHandle = {
        config: {
            utk: false,
            dbconf_name: 'fast_bg',
            rbac_role_tableName: '$rbac_role_tableName',
            dbdata_dbconf_tableName: '$dbdata_dbconf_tableName',
            dbdata_column_tableName: '$dbdata_column_tableName',
            dbdata_table_tableName: '$dbdata_table_tableName',
            mainTable_tableName: false,
            mainTable_dbConfCode: false,

        },
    };

    apiHandle.setUserToken = (utk) => {
        apiHandle.config.utk = utk;
        return apiHandle;
    };
    apiHandle.setMainTableName = (mainTable_dbConfCode, mainTable_tableName) => {
        apiHandle.config.mainTable_tableName = mainTable_tableName;
        apiHandle.config.mainTable_dbConfCode = mainTable_dbConfCode;
        return apiHandle;
    };
    /**
     * 垂直方向取值表
     * @param rowItems
     * @param columnItems
     */
    apiHandle.createVerticalTable = (rowItems, columnItems) => {
        let vertical_table = new Emt('table').setAttrsByStr('class="table table-bordered table-striped table-hover"');
        vertical_table.columnItems = columnItems;
        vertical_table.columnItemMap = {};

        let vertical_table_header = vertical_table.createTHead();
        let header_tr = vertical_table_header.insertRow();

        header_tr.insertCell().textContent = '#';
        vertical_table.columnItems.forEach((columnItem) => {
            let tmp_td = header_tr.insertCell();
            tmp_td.textContent = columnItem.text;
            vertical_table.columnItemMap[columnItem.val] = {checkboxs: []};
        });

        let vertical_table_body = vertical_table.createTBody();


        rowItems.forEach(function (rowItem) {
            let body_tr = vertical_table_body.insertRow();
            body_tr.insertCell().textContent = rowItem.text;
            vertical_table.columnItems.forEach((columnItem) => {
                let tmp_td = body_tr.insertCell();
                //  tmp_td.textContent = columnItem.text;
                let checkbox = new Emt('input', 'type="checkbox"', '', {value: rowItem.val});
                vertical_table.columnItemMap[columnItem.val].checkboxs.push(checkbox);
                tmp_td.append(new Emt('label', '', '#').addNodes([checkbox, new Emt('span', '', '#####')]));
            });

        });
        //比如加载   {read_role_codes:[admin,dev],update_role_codes:[],}
        vertical_table.loadColumnIndexVals = (dataInfo) => {
            vertical_table.columnItems.forEach((columnItem) => {
                if (dataInfo[columnItem.val] !== undefined && typeof dataInfo[columnItem.val].forEach === 'function') {
                    vertical_table.columnItemMap[columnItem.val].checkboxs.forEach((checkbox) => {
                        checkbox.checked = dataInfo[columnItem.val].indexOf(checkbox.value) > -1;
                    })
                }
            });
        };
        vertical_table.getData = () => {
            let res = {};
            vertical_table.columnItems.forEach((columnItem) => {
                res[columnItem.val] = [];
                vertical_table.columnItemMap[columnItem.val].checkboxs.forEach((checkbox) => {
                    if (checkbox.checked) {
                        res[columnItem.val].push(checkbox.value);
                    }
                });
            });
            return res;
        };
        return vertical_table;
    };


    apiHandle.initRoles = (fun) => {
        return kl.ajax({
            url: '/_dp/v1/dbdata/select?user_token=' + apiHandle.config.utk,
            data: {dbconf_name: apiHandle.config.dbconf_name, table_name: apiHandle.config.rbac_role_tableName, page_index: 1, page_size: 1000},
            type: 'json',
            async: true,
        }).then(res => {
            if (kl.isUndefined(res, 'result.data.dataRows') || typeof res.result.data.dataRows.forEach !== "function") {
                alert('init role 失败:' + (kl.isUndefined(res, 'result.msg') ? '未知' : res.result.msg));
            } else {
                window.serverData.dataLib.role.list = res.result.data.dataRows;
                window.serverData.dataLib.role.codeItems = [];
                window.serverData.dataLib.role.codeItemMap = {};

                window.serverData.dataLib.role.list.forEach((roleInfo) => {
                    window.serverData.dataLib.role.items.push({val: roleInfo.id, text: roleInfo.role_name});
                    window.serverData.dataLib.role.map[roleInfo.id] = roleInfo.role_name;

                    window.serverData.dataLib.role.codeItems.push({val: roleInfo.role_code, text: roleInfo.role_name});
                    window.serverData.dataLib.role.codeItemMap[roleInfo.role_code] = roleInfo.role_name;
                });
                if (typeof fun === "function") {
                    fun();
                }
            }
            return apiHandle;
        });
    };

    apiHandle.initDbConfs = (fun) => {
        return kl.ajax({
            url: '/_dp/v1/dbdata/select?user_token=' + apiHandle.config.utk,
            data: {dbconf_name: apiHandle.config.dbconf_name, table_name: apiHandle.config.dbdata_dbconf_tableName, page_index: 1, page_size: 1000},
            type: 'json',
            async: true,
        }).then(res => {
            if (kl.isUndefined(res, 'result.data.dataRows') || typeof res.result.data.dataRows.forEach !== "function") {
                alert('init role 失败:' + (kl.isUndefined(res, 'result.msg') ? '未知' : res.result.msg));
            } else {
                window.serverData.dataLib.dbconf = window.serverData.dataLib.dbconf || {};
                window.serverData.dataLib.dbconf.list = res.result.data.dataRows;

                window.serverData.dataLib.role.list.forEach((dbConfInfo) => {
                    window.serverData.dataLib.dbconf.items.push({val: dbConfInfo.id, text: dbConfInfo.db_code});
                    window.serverData.dataLib.dbconf.map[dbConfInfo.id] = dbConfInfo.db_code;
                });
                if (typeof fun === "function") {
                    fun();
                }
            }
            return apiHandle;
        });
    };


    apiHandle.initTableInfo = async (dbconf_code, table_name, dataLibIndex, fun) => {
        return kl.ajax({
            url: '/_dp/v1/dbdata/info?user_token=' + apiHandle.config.utk,
            data: {dbconf_name: dbconf_code || apiHandle.config.dbconf_name, table_name: table_name},
            type: 'json',
            async: true,
        }).then(res => {
            if (kl.isUndefined(res, 'result.data.columns') || kl.isUndefined(res, 'result.data.table')) {
                alert('新增menu失败:' + (kl.isUndefined(res, 'result.msg') ? '未知' : res.result.msg));
            } else {
                window.serverData.dataLib[dataLibIndex] = window.serverData.dataLib[dataLibIndex] || {};
                window.serverData.dataLib[dataLibIndex].tableInfo = res.result.data.table;
                window.serverData.dataLib[dataLibIndex].__column = {map: {}, items: res.result.data.columns};

                res.result.data.columns.forEach(function (colInfo) {
                    let tmp = kl.jsonDecode(colInfo.val_items, []);
                    console.log(tmp, 'x', colInfo.val_items);
                    colInfo.valItemMap = {};
                    tmp.forEach((itemInfo) => {
                        colInfo.valItemMap[itemInfo.val] = itemInfo.text;
                    });
                    colInfo.valItems = tmp;
                    window.serverData.dataLib[dataLibIndex].__column.map[colInfo.column_name] = colInfo;


                    if (colInfo.index_key === 'PRI') {
                        window.serverData.dataLib[dataLibIndex].tableInfo.pkKey = colInfo.column_name;
                    }


                });
                if (typeof fun === "function") {
                    fun();
                }

            }
            return apiHandle;
            //return res.isOk;
        });
    };


    apiHandle.init_DbConnectConf_ConfigInfo = async (fun) => {
        return apiHandle.initTableInfo(false, apiHandle.config.dbdata_dbconf_tableName, 'dbdataDbConfTable', function () {
            if (typeof fun === "function") {
                fun();
            }
        });
    };

    apiHandle.init_MainTable_ConfigInfo = async (fun) => {
        return apiHandle.initTableInfo(apiHandle.config.mainTable_dbConfCode, apiHandle.config.mainTable_tableName, 'mainTable', function () {
            if (typeof fun === "function") {
                fun();
            }
        });

    };
    apiHandle.init_DbTableConf_ConfigInfo = async (fun) => {
        return apiHandle.initTableInfo(false, apiHandle.config.dbdata_table_tableName, 'dbdataTablesTable', function () {
            if (typeof fun === "function") {
                fun();
            }
        });
    };
    apiHandle.init_DbColumnConf_ConfigInfo = async (fun) => {
        return apiHandle.initTableInfo(false, apiHandle.config.dbdata_column_tableName, 'dbdataColumnTable', function () {
            if (typeof fun === "function") {
                fun();
            }
        });

    };

    return apiHandle;
};
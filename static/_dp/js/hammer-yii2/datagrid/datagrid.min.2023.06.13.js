/**
 *
 * @param input_opt  {
 * csrf:'string',
 * title:'title',
 * }
 * @returns {{hide: hide, show: show}}
 */
let hammerBootstarpAsyncDatagrid = function (input_param) {
    let dataGrid = {
        api: {
            // reloadDataRows: false,
            //  requestData: false,
            //getRequestParam: false,
        },
        ele: {},
        param: {
            attr: {},
            page: {
                index: 1,
                size: 20,
            },
            sort: {}
        },
        dataTrs: [],
        processData: {
            sourceData: [],
            displayData: [],
            rawDataJsonStrs: [],
            tableDataJsonStrs: [],
        }
    };


    let config = {
        container: false,
        components: {//部件，datagrid 默认的部件
            totalInformation: true,//是否展示 数据多少行 多少页信息，自定义的话，可以在request.after定义
            pageInfomation: true,//分页操作
            requestInformation: true,//请求信息，条件 排序 分期
        },
        dataSource: {//请求设置
            url: "",
            //接受 post 请求的
            //发送请求 {page_index: x, page_size: y,attr:{},sort:{field:type}} ，然后会追加上 data.append,并且将其当前post_data 输入给 data.fun 再次加工
            // 返回数据必须满足 {"data":{"rowsTotal":11,"pageTotal":1,"pageIndex":1,"pageSize":1000,"dataRows":[]},
            downloadable: false,//是否 可下载数据
            xhr: {
                timeout: 60,
                headers: [],
            },
            beforeRequest: (event_type, dataGrid) => {
                return true;//在xhr 请求之前  做些什么，如果要请求，返回true，不想继续请求 直接false 比如不想请求的太频繁，过掉某些事件
            },
            param: {
                append: {
                    //附加额外的请求参数 {k:v}
                },
                fun: (post_data) => {
                    return post_data;//处理请求参数，如果有必要的话
                },
            },
            resultAdapter: (request_res) => {//结果转化器
                //将返回数据整理成 要求的格式 {"data":{"rowsTotal":11,"pageTotal":1,"pageIndex":1,"pageSize":1000,"dataRows":[]},
                return request_res;
            },
            afterRequest: (request_res, dataGrid) => {
                //这个只是调用，不关心返回
            },
            requestButtons: [
                true,//发请求事件的按钮，第一个代表是否自动生成
            ],
        },
        paramPreset: {//在初始化之前设置，以这些信息进行初始化
            attr: {//过滤器
            },
            page: {//分页
                index: 1,
                size: 20,
                sizes: [],//如果sizes 里面有值，就用select，不给自由输入了
            },
            sort: {//排序
            }
        },
        columns: [
            {
                handleKey: 'id',//句柄
                attrKey: 'id',//数据下标
                headerText: 'ID',
                sortable: true,
                filter: {//过滤器/筛选器
                    inputs: [
                        true,//第一个代表 header row 里面的，以 true或false 表示要不要生成
                        //xxxx// 要具体的 html element了，必须要实现 getVal/setVal/setOnChange 方法,如果是select或者其他多选类型，必须得实现setItems
                    ],
                    config: {
                        valueItems: [],//[ {text:xx,value:xx} ]  ，空代表随便填 input text， 如果不为空代表从里面选择 select ，长度超过 20 datalist
                    }
                },
                info: {},
                fun: false,//渲染方法
            },
        ],
    };
    config.columns = [];
    if (input_param.container !== undefined) {
        config.container = input_param.container;
    }
    if (input_param.components === undefined) {
        console.log('所有组件都展示');
    } else {
        config.components.pageInfomation = input_param.components.pageInfomation || false;
        config.components.totalInformation = input_param.components.totalInformation || false;
        config.components.requestInformation = input_param.components.requestInformation || false;
    }
    if (input_param.dataSource === undefined) {
        throw 'dataSource 必须设置!';
    } else if (input_param.dataSource === false) {
        console.warn("config.dataSource 自定义");
    } else {
        if (typeof input_param.dataSource.url !== "string") {
            throw 'config.dataSource.url 必须是有效的string';
        }
        config.dataSource.url = input_param.dataSource.url;

        if (typeof input_param.dataSource.downloadable !== "boolean") {
            throw 'config.dataSource.downloadable 必须是boolean';
        }
        config.dataSource.downloadable = input_param.dataSource.downloadable;


        if (input_param.dataSource.xhr === undefined) {
            console.log("config.dataSource.xhr 没设置，将用默认的");
        } else {
            if (typeof input_param.dataSource.xhr.timeout === "number") {
                config.dataSource.xhr.timeout = input_param.dataSource.xhr.timeout;
            } else {
                console.warn("config.dataSource.xhr.timeout  没有追加");
            }
            if (typeof input_param.dataSource.xhr.headers === "object" && typeof input_param.dataSource.xhr.headers.forEach === "function") {
                config.dataSource.xhr.headers = input_param.dataSource.xhr.headers;
            } else {
                console.warn("config.dataSource.xhr.headers 没有追加");
            }
        }


        if (typeof input_param.dataSource.beforeRequest === "function") {
            config.dataSource.beforeRequest = input_param.dataSource.beforeRequest;
        } else {
            console.warn("config.dataSource.beforeRequest 请求之前处理 不是函数");
        }
        if (typeof input_param.dataSource.resultAdapter === "function") {
            config.dataSource.resultAdapter = input_param.dataSource.resultAdapter;
        } else {
            console.warn("config.dataSource.resultAdapter 请求结果转化 不是函数");
        }
        if (typeof input_param.dataSource.afterRequest === "function") {
            config.dataSource.afterRequest = input_param.dataSource.afterRequest;
        } else {
            console.warn("config.dataSource.afterRequest 请求之后调用 不是函数");
        }
        if (input_param.dataSource.param === undefined) {
            console.log("config.dataSource.param 没有另外追加参数");
        } else {
            if (typeof input_param.dataSource.param.append === "object") {
                config.dataSource.param.append = input_param.dataSource.param.append;
            } else {
                console.warn("config.dataSource.param.append 没有追加");
            }
            if (typeof input_param.dataSource.param.fun === "function") {
                config.dataSource.param.fun = input_param.dataSource.param.fun;
            } else {
                console.warn("config.dataSource.param.fun 没有追加");
            }
        }

        if (input_param.dataSource.requestButtons !== undefined && typeof input_param.dataSource.requestButtons.forEach === "function" && input_param.dataSource.requestButtons.length > 0) {
            config.dataSource.requestButtons = input_param.dataSource.requestButtons;
        } else {
            console.warn("config.dataSource.requestButtons 没有配置");
        }
    }


    if (input_param.paramPreset === undefined) {
        //  throw 'dataSource 必须设置!';
    } else if (input_param.paramPreset === false) {
        console.warn("config.paramPreset 自定义");
    } else {
        if (input_param.paramPreset.attr === undefined) {
            console.warn(" config.paramPreset.attr 没有设置");
        } else {
            if (typeof input_param.paramPreset.attr === "object") {
                config.paramPreset.attr = input_param.paramPreset.attr;
                dataGrid.param.attr = config.paramPreset.attr;
            } else {
                console.warn(" config.paramPreset.attr 没有设置");
            }
        }

        if (input_param.paramPreset.sort === undefined) {
            console.warn(" config.paramPreset.sort 没有设置");
        } else {
            if (typeof input_param.paramPreset.sort === "object") {
                config.paramPreset.sort = input_param.paramPreset.sort;
                dataGrid.param.sort = config.paramPreset.sort;
            } else {
                console.warn(" config.paramPreset.sort 没有设置");
            }
        }

        if (input_param.paramPreset.page === undefined) {
            console.warn(" config.paramPreset.page 没有设置");
        } else {
            if (typeof input_param.paramPreset.page === "object") {
                //  config.paramPreset.page = input_param.paramPreset.page;

                if (typeof input_param.paramPreset.page.index === "number" && input_param.paramPreset.page.index > 0) {
                    config.paramPreset.page.index = input_param.paramPreset.page.index;
                    dataGrid.param.page.index = input_param.paramPreset.page.index;
                } else {
                    config.paramPreset.page.index = 1;
                    dataGrid.param.page.index = config.paramPreset.page.index;
                    console.warn(" config.paramPreset.page.index 没有设置");
                }
                if (typeof input_param.paramPreset.page.size === "number" && input_param.paramPreset.page.size > 0) {
                    config.paramPreset.page.size = input_param.paramPreset.page.size;
                } else {
                    config.paramPreset.page.size = 20;
                    dataGrid.param.page.size = config.paramPreset.page.size;
                    console.warn(" config.paramPreset.page.size 没有设置");
                }


                if (typeof input_param.paramPreset.page.sizes === "object" && typeof input_param.paramPreset.page.sizes.forEach === "function" && input_param.paramPreset.page.sizes.length > 0) {
                    config.paramPreset.page.sizes = input_param.paramPreset.page.sizes;
                } else {
                    config.paramPreset.page.sizes = [];
                    dataGrid.param.page.sizes = config.paramPreset.page.sizes;
                    console.warn(" config.paramPreset.page.sizes 没有设置");
                }
            } else {
                console.warn(" config.paramPreset.page.data 没有设置");
            }
        }
        console.log(config.paramPreset, dataGrid.param);
    }


    if (input_param.columns !== undefined && typeof input_param.columns.forEach === "function" && input_param.columns.length > 0) {
        config.columns = input_param.columns;
    } else {
        console.warn("config.columns 没有配置");
    }
    let columnCheckConfig = {
        attr: 'root',
        check: {
            must: true,
            type: ['object'],
            objectAttrs: [
                {attr: 'handleKey', check: {must: true, type: ['string']}},//句柄
                {attr: 'attrKey', check: {must: true, type: ['string', 'boolean']}},//数据下标
                {attr: 'headerText', check: {must: true, type: ['string']}},
                {attr: 'sortable', check: {must: true, type: ['boolean']}},
                {
                    attr: 'filter',
                    check: {
                        must: false,
                        type: ['boolean', 'object'],
                        objectAttrs: [
                            {
                                attr: 'inputs',
                                check: {
                                    must: true,
                                    type: ['array'],
                                    arrayElementConfig: {
                                        check: {
                                            type: ['boolean', 'object'],
                                            objectAttrs: [
                                                // {attr: 'tagName', check: {must: true, type: ['string']}},
                                            ]
                                        }
                                    },
                                }
                            },
                            {
                                attr: 'config',
                                check: {
                                    must: true,
                                    type: ['object'],
                                    objectAttrs: [
                                        {
                                            attr: 'valueItems',
                                            check: {
                                                must: true,
                                                type: ['array'],
                                                arrayElementConfig: {
                                                    check: {
                                                        type: ['object'],
                                                        objectAttrs: [
                                                            {attr: 'val', check: {must: true, type: ['string', 'number']}},
                                                            {attr: 'text', check: {must: true, type: ['string', 'number']}}
                                                        ]
                                                    }
                                                }
                                            },
                                        }
                                    ]
                                }
                            },
                        ]
                    }
                },
                {attr: 'info', check: {must: false, type: ['object'], objectAttrs: []}},
                {attr: 'fun', check: {must: true, type: ['boolean', 'function']}},
            ]
        }
    };

    let adaptedXhrDataCheckConfig = {
        attr: 'root',
        check: {
            must: true,
            type: ['object'],
            objectAttrs: [
                {attr: 'pageIndex', detail: '当前页码', check: {must: true, type: ['number'], min: 1}},//
                {attr: 'pageTotal', detail: '总页数', check: {must: true, type: ['number',], min: 0}},//
                {attr: 'pageSize', detail: '每页容量，多少行', check: {must: true, type: ['number',], min: 1, max: 100000}},//
                {attr: 'rowsTotal', detai: '总行数', check: {must: true, type: ['number',], min: 0,}},//
                {attr: 'dataRows', detai: '行数据', check: {must: true, type: ['array'], arrayElementConfig: {check: {type: 'object', objectAttrs: []}}}},
            ]
        }
    };


    input_param.columns.forEach((columnConfig, columnConfigIndex) => {
        let check_res = kl.dataBasicCheck(columnConfig, columnCheckConfig);
        if (check_res !== true) {
            console.error(columnConfig, check_res);
            throw `列:[${columnConfigIndex}] 配置错误`;
        }
    });
    config.columns = input_param.columns;


    //tableTpart
    dataGrid.ele.Table = new Emt('table', '' +
        'class="table table-bordered table-striped table-hover" ' +
        'style="' +
        //'table-layout: fixed; ' +
        'word-break:break-all;' +
        'word-wrap:break-word;' +
        'border-collapse: collapse;' +
        'table-layout: fixed;' +
        'width:100%;' +
        '"'
    );
    dataGrid.ele.THead = dataGrid.ele.Table.createTHead();
    dataGrid.ele.TBody = dataGrid.ele.Table.createTBody();
    dataGrid.ele.TFoot = dataGrid.ele.Table.createTFoot();
    dataGrid.ele.TCaption = dataGrid.ele.Table.createCaption();


    dataGrid.ele.requestBtn = new Emt('button').setPros({className: 'btn btn-info', textContent: '查询'});
    dataGrid.ele.toggleTableWidthBtn = new Emt('button').setPros({className: 'btn btn-info', textContent: 'table铺开'});

    dataGrid.ele.topDiv = new Emt('DIV', 'class="datagrid-top-div" id="datagrid-top-div"', '');
    dataGrid.ele.pagination = new Emt('DIV', 'class="datagrid-pagination-div" id="datagrid-pagination-div"', '');
    dataGrid.ele.pagination.gotoStart = new Emt('span', 'href="#" style="cursor: pointer"', '«', {goto: 1});
    dataGrid.ele.pagination.gotoEnd = new Emt('span', 'href="#" style="cursor: pointer"', '»', {goto: 'end'});
    dataGrid.ele.pagination.infoDataSpan = new Emt('span', 'class="input-group-addon"', '');
    dataGrid.ele.pagination.pageBtns = [];
    for (let i = 1; i < 6; i++) {
        let a = new Emt('span', 'href="#" style="cursor: pointer"', i.toString(), {goto: i});
        dataGrid.ele.pagination.pageBtns.push(a);
        a.setStyleActive = () => {
            dataGrid.ele.pagination.pageBtns.forEach((tmp_a) => {
                tmp_a.removeStyleActive();
            });
            a.parentElement.classList.add('active');
        }
        a.removeStyleActive = () => {
            a.parentElement.classList.remove('active');
        };
        a.setStyleDisable = () => {
            a.parentElement.classList.add('disabled');
            a.parentElement.classList.add('hide');
        };
        a.removeStyleDisable = () => {
            a.parentElement.classList.remove('disabled');
            a.parentElement.classList.remove('hide');

        };
    }
    if (config.paramPreset.page.sizes.length > 0) {
        dataGrid.ele.pagination.pageSizeInput = new Emt('select', 'type="number" class="form-control "  style=" display: inline;width:6em ; "', '',);
        config.paramPreset.page.sizes.forEach((page_size) => {
            dataGrid.ele.pagination.pageSizeInput.add(new Option(page_size, page_size));
        });

        dataGrid.ele.pagination.pageSizeInput.value = config.paramPreset.page.size;
    } else {
        dataGrid.ele.pagination.pageSizeInput = new Emt('input', 'type="number" class="form-control "  style=" display: inline;width:6em ; "', '', {value: config.paramPreset.page.size});
    }

    dataGrid.ele.pagination.pageIndexInput = new Emt('input', 'type="number" class="form-control "  style=" display: inline; width:5em;  "', '', {value: config.paramPreset.page.index});
    dataGrid.ele.pagination.pageJumpBtn = new Emt('button', 'type="button" class="btn btn-info"', '跳转至');

    dataGrid.ele.pagination.pageBtns.concat([dataGrid.ele.pagination.gotoStart, dataGrid.ele.pagination.gotoEnd]).forEach((pageGotoBtn) => {
        pageGotoBtn.addEventListener('click', function () {
            // pageGotoBtn.setStyleActive();
            dataGrid.ele.pagination.pageIndexInput.value = this.goto;
            dataGrid.api.jumpPageTo();
        });
    })
    dataGrid.ele.pagination.pageJumpBtn.addEventListener('click', function () {
        dataGrid.api.jumpPageTo();
    });


    dataGrid.ele.pagination.addNodes([
        new Emt('div', 'class="col-xs-3"').addNodes([
            new Emt('UL', 'class="pagination"', '').addNodes([
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.gotoStart]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.pageBtns[0]]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.pageBtns[1]]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.pageBtns[2]]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.pageBtns[3]]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.pageBtns[4]]),
                new Emt('LI', '', '').addNodes([dataGrid.ele.pagination.gotoEnd]),
                new Emt('LI', '', '').addNodes([])
            ]),
        ]),
        new Emt('div', 'class="col-sm-9"').addNodes([

            new Emt('div', 'class="input-group" style="margin:20px 0;float:left;width:19em;"').addNodes([
                dataGrid.ele.pagination.infoDataSpan,
                dataGrid.ele.pagination.pageSizeInput,
                new Emt('span', 'class="input-group-addon"', '行/页'),
            ]),
            new Emt('div', 'class="input-group" style="margin:20px 0;float:left;width:10em;margin-left:1em;"').addNodes([
                new Emt('span', 'class="input-group-btn"').addNodes([
                    dataGrid.ele.pagination.pageJumpBtn
                ]),
                dataGrid.ele.pagination.pageIndexInput,
                new Emt('span', 'class="input-group-addon"', '页'),
            ]),
        ]),
        //new Emt('div', 'class="col-sm-2"').addNodes([]),

    ]);
    dataGrid.addTr = function (tableTpart) {
        let new_tr = tableTpart.insertRow();
        new_tr.apiHandle = {
            td: {map: {}, list: []}
        };
        new_tr.addTd = function (cellIndexKey, cellAttrs) {
            let new_td = new_tr.insertCell();
            new_tr.apiHandle.td.list.push(new_td);
            if (typeof cellIndexKey === 'string') {
                new_tr.apiHandle.td.map[cellIndexKey] = new_td;
            }
            if (typeof cellAttrs === 'object') {
                for (var attr in cellAttrs) {
                    new_td[attr] = cellAttrs[attr];
                }
            }
            new_td.addNodes = function (nodes) {
                nodes.forEach((td_node) => {
                    new_td.appendChild(td_node);
                });
                return new_td;
            };

            return new_td;
        }
        return new_tr;
    }


    dataGrid.topTr = dataGrid.addTr(dataGrid.ele.THead);
    dataGrid.titleTr = dataGrid.addTr(dataGrid.ele.THead);
    dataGrid.filterTr = dataGrid.addTr(dataGrid.ele.THead);
    dataGrid.footTr = dataGrid.addTr(dataGrid.ele.TFoot);


    dataGrid.footTr.addTd('page').addNodes([]);


    dataGrid.filter_eles = {keys: [], ele_map: {}, btns: []};


    /**
     * 设置 获取数据函数   参数: event_type  requestParam  reloadDataRowsFunction
     *
     * @param fun
     */
    dataGrid.api.setRequestDataRowsFunction = (fun) => {
        dataGrid.set.requestDataRowsFunction = fun;
        return dataGrid;
    }

    /**
     * 执行/展示  datagrid
     */
    dataGrid.api.init = () => {


        config.container.appendChild(
            new Emt('div', 'class="table-responsive" ').addNodes([
                dataGrid.ele.topDiv.addNodes([
                    dataGrid.ele.toggleTableWidthBtn,
                    dataGrid.ele.requestBtn,
                ]),
                dataGrid.ele.Table,
                dataGrid.ele.pagination,
            ])
        );
        config.container.datagrid = dataGrid;
        return dataGrid;
    }

    /**
     * 对外开放 ，获取 页面参数
     * @returns {{filter: {}, sort: {type, key: *}, page: {size, index}}}
     */
    dataGrid.api.getRequestParam = () => {
        let requestParam = {
            sort: dataGrid.param.sort,
            page: {size: dataGrid.ele.pagination.pageSizeInput.value, index: dataGrid.ele.pagination.pageIndexInput.value},
            filter: dataGrid.param.attr
        };


        console.log('datagrid.getRequestParam', requestParam);

        return requestParam;
    }


    dataGrid.api.jumpPageTo = function () {
        dataGrid.api.requestData('page');
    }
    dataGrid.resortFun = function (btn_ele) {
        dataGrid.api.requestData('sort');
    }
    dataGrid.filterChange = function (btn_ele) {
        dataGrid.api.requestData('filter');
    }
    dataGrid.api.toggleTableWidth = () => {
        //filterInput.dispatchEvent(new CustomEvent('change'));
        dataGrid.ele.toggleTableWidthBtn.dispatchEvent(new CustomEvent('click'));
    }


    dataGrid.addDataRow = function (rowData) {
        let dataTr = dataGrid.addTr(dataGrid.ele.TBody);
        dataGrid.dataTrs.push(dataTr);
        dataTr.rowData = rowData;
        config.columns.forEach(function (columnConfig) {
            let columnKey = columnConfig.handleKey;
            let data_cell = dataTr.addTd(columnKey);
            if (columnConfig.attrKey === false) {
                columnConfig.fun(data_cell, rowData);
            } else {
                if (rowData[columnKey] === undefined) {
                    columnConfig.fun === false ? (data_cell.textContent = 'undefined') : columnConfig.fun(data_cell, rowData);
                } else {
                    if (columnConfig.fun === false) {
                        data_cell.innerText = rowData[columnKey] === null ? '' : rowData[columnKey].toString();
                    } else {
                        columnConfig.fun(data_cell, rowData);
                    }
                }
            }
        })
    }
    dataGrid.clearDataRows = function () {
        let max = dataGrid.dataTrs.length;
        for (let i = 0; i < max; i++) {
            //console.log(dataGrid.dataTrs.length);
            dataGrid.dataTrs[i].remove();
        }
        dataGrid.dataTrs = [];
        dataGrid.processData.displayData = [];
        dataGrid.processData.sourceData = [];
        dataGrid.processData.rawDataJsonStrs = [];
        dataGrid.processData.tableDataJsonStrs = [];

    }
    dataGrid.flushPager = function (resData) {
        //onsole.log(resData);
        dataGrid.ele.pagination.infoDataSpan.textContent = '当前页码' + resData.pageIndex + '/' + resData.pageTotal + '.        .' + ' 共' + resData.rowsTotal + '条';
        let pageTotal = resData.pageTotal;
        let pageIndex = resData.pageIndex;
        dataGrid.ele.pagination.gotoEnd.goto = pageTotal;
        // console.log(pageTotal, pageIndex);
        for (let tmp_i = 0; tmp_i < 5; tmp_i++) {
            dataGrid.ele.pagination.pageBtns[tmp_i].setStyleDisable();
        }
        let pageNums = [];
        let tmpPageNums = [pageIndex - 2, pageIndex - 1, pageIndex, pageIndex + 1, pageIndex + 2];
        tmpPageNums.forEach((tmpNum) => {
            if (tmpNum > 0 && tmpNum <= pageTotal) {
                pageNums.push(tmpNum);
            }
        });
        // console.log(pageIndex, tmpPageNums, pageNums);
        pageNums.forEach((pageNum, pageNumIndex) => {
            dataGrid.ele.pagination.pageBtns[pageNumIndex].goto = pageNum;
            dataGrid.ele.pagination.pageBtns[pageNumIndex].textContent = pageNum.toString();
            dataGrid.ele.pagination.pageBtns[pageNumIndex].removeStyleDisable();
            if (pageNum === pageIndex) {
                dataGrid.ele.pagination.pageBtns[pageNumIndex].setStyleActive();
            }
        });

    }
    /**
     * 重新载入数据
     * @param row_datas
     */
    dataGrid.api.reloadDataRows = function (data) {
        dataGrid.clearDataRows();
        if (config.dataSource.downloadable || config.dataSource === false) {
            dataGrid.processData.sourceData = data.dataRows;
            let attrKeys = [];
            let headerMap = {};
            config.columns.forEach((columnConfig) => {
                if (columnConfig.attrKey !== false) {
                    attrKeys.push(columnConfig.attrKey);
                    headerMap[columnConfig.attrKey] = columnConfig.headerText;
                }
            });
            dataGrid.processData.rawDataJsonStrs.push(JSON.stringify(headerMap));

            data.dataRows.forEach(function (row_data) {

                let tmpObj = {};
                attrKeys.forEach((attrKey) => {
                    tmpObj[attrKey] = row_data[attrKey];
                });
                dataGrid.processData.rawDataJsonStrs.push(JSON.stringify(tmpObj));

                dataGrid.addDataRow(row_data);
            });
        } else {
            data.dataRows.forEach(function (row_data) {
                dataGrid.addDataRow(row_data);
            });
        }

        // dataGrid.topTr.apiHandle.td.map.goto_submit.setAttribute('colspan', dataGrid.column.keys.length);
        //  dataGrid.footTr.apiHandle.td.map.page.setAttribute('colspan', dataGrid.column.keys.length);
        if (config.components.pageInfomation === true) {
            dataGrid.flushPager(data);
        }
    }


    dataGrid.api.requestData = function (event_type) {
        console.log('event_type', event_type);

        let page_data = dataGrid.api.getRequestParam();
        // console.log('\nsetRequestDataRowsFunction:<<<\n', 'event_type', event_type, 'post_data', page_data, '\n>> setRequestDataRowsFunction\n');
        //  if (event_type === 'filter') {
        //      console.log('这个不做理会，不然请求太频繁了');
        //      return false;
        //  }
        let post_data = {
            page_index: page_data.page.index,
            page_size: page_data.page.size,
            attr: page_data.filter,
            sort: page_data.sort
        };
        for (let key in config.dataSource.param.append) {
            post_data[key] = config.dataSource.param.append[key];
        }
        if (config.dataSource.param.fun !== false) {
            post_data = config.dataSource.param.fun(post_data);
        }
        // post_data.attr.table_name = window.serverData.table_name;


        for (let attr_key in post_data.attr) {
            // console.log(attr_key);
            if (post_data.attr[attr_key][0] === ':') {
                post_data.attr[attr_key] = 'like:%' + post_data.attr[attr_key].substring(1) + '%';
            }
        }
        if (config.dataSource.beforeRequest(event_type, dataGrid) === true) {
            (async function () {
                let tmp = async () => {
                    await kl.ajax({
                        url: config.dataSource.url,
                        data: post_data,
                        //  headers: [['X-CSRF-TOKEN', window.opInfo.csrf]],
                        type: 'json',
                        timeout: config.dataSource.xhr.timeout,
                        headers: config.dataSource.xhr.headers,
                        async: true,
                    }).then(res => {
                        console.log('res:', res);
                        let res_request_data = res.result;
                        if (res.isOk === false) {
                            alert(res.msg || '请求异常');
                        }
                        let resData = config.dataSource.resultAdapter(res_request_data);

                        let check_res = kl.dataBasicCheck(resData, adaptedXhrDataCheckConfig);
                        if (check_res === true) {

                            dataGrid.api.reloadDataRows(resData);

                            // console.log('请求成功，调用config.dataSource.afterRequest(request_res, dataGrid) ，在获取参数渲染之后，再处理其他事情');
                            config.dataSource.afterRequest(res_request_data, dataGrid);
                        } else {
                            console.error(resData, check_res);
                            // throw `xhr 请求数据结构错误`;
                        }
                        return res.isOk;
                    });
                };
                await tmp();

            })();

            if (0) {
                kl.ajax({
                    url: config.dataSource.url,
                    data: post_data,
                    type: 'json',
                    success: function (res_request_data) {
                        //console.log('请求成功，调用config.dataSource.resultAdapter() 获取指定格式');
                        let resData = config.dataSource.resultAdapter(res_request_data);
                        if (resData && typeof resData.pageIndex === "number" && typeof resData.rowsTotal === "number" && resData.dataRows && typeof resData.dataRows.forEach === 'function') {
                            dataGrid.ele.pagination.infoDataSpan.textContent = '当前页码' + resData.pageIndex + '/' + resData.pageTotal + '.        .' + ' 共' + resData.rowsTotal + '条';
                            dataGrid.api.reloadDataRows(resData);

                            // console.log('请求成功，调用config.dataSource.afterRequest(request_res, dataGrid) ，在获取参数渲染之后，再处理其他事情');
                            config.dataSource.afterRequest(res_request_data, dataGrid);
                        }


                    },
                    error: function (res_request_data) {
                        console.log('datagrid.api.setRequestDataRowsFunction 网络异常:' + res_request_data);
                        alert('网络异常');
                    }
                });
            }
        }


    };


    config.columns.forEach((columnConfig, columnConfigIndex) => {
        let headerTextCell = dataGrid.titleTr.addTd(columnConfig.handleKey);
        let filterCell = dataGrid.filterTr.addTd(columnConfig.handleKey);
        headerTextCell.resizeDivs = [];
        let spans = columnConfig.sortable === true ? [
            new Emt('SPAN', 'class=""', columnConfig.headerText),
            new Emt('span', 'class="glyphicon glyphicon-sort-by-attributes-alt hidden"', '', {sortType: 'desc'}),
            new Emt('span', 'class="glyphicon glyphicon-arrow-up hidden"', '', {sortType: 'asc'}),
            //new Emt('span', 'class="glyphicon glyphicon glyphicon-remove"', '', {sortType: false}),
            // new Emt('SPAN', 'class="caret"'),
        ] : [new Emt('SPAN', 'class=""', columnConfig.headerText)];
        let descSortBtn = new Emt('button', 'type="button" class="btn btn-xs btn-default glyphicon glyphicon-sort-by-attributes-alt" role="menuitem" tabindex="-1" href="#"', '降序排列', {sortType: 'desc'});
        let ascSortBtn = new Emt('button', 'type="button" class="btn btn-xs btn-default glyphicon glyphicon-arrow-up" role="menuitem" tabindex="-1" href="#"', '升序排列', {sortType: 'asc'});
        let noSortBtn = new Emt('button', 'type="button" class="btn btn-xs btn-default glyphicon glyphicon-remove" role="menuitem" tabindex="-1" href="#"', '不参与', {sortType: false});
        let headerButtonDiv = new Emt('DIV', 'class="dropdown"').addNodes([
            new Emt('BUTTON', `type="button" class="btn btn-md btn-default  dropdown-toggle" id="headertext_${columnConfig.handleKey}" data-toggle="dropdown"`, '').addNodes(spans),
            new Emt('UL', `class="dropdown-menu" role="menu" aria-labelledby="headertext_${columnConfig.handleKey}"`).addNodes([
                new Emt('LI', 'class="divider"'),
                new Emt('LI', 'role="presentation"').addNodes([descSortBtn]),
                new Emt('LI', 'class="divider"'),
                new Emt('LI', 'role="presentation"').addNodes([noSortBtn]),
                new Emt('LI', 'class="divider"'),
                new Emt('LI', 'role="presentation"').addNodes([ascSortBtn]),
                new Emt('LI', 'class="divider"'),
            ])
        ]);
        let headerDiv = new Emt('div', 'style="' +
            // 'float:left;' +
            'overflow:visible;' +
            'display: flex;' +
            'width:100%;' +
            'height:2.5em;' +
            '"');
        headerTextCell.resizeBox = new Emt('button', 'style="' +
            'float:right;' +
            'min-height:100%;' +
            'min-width:1px;' +
            'cursor: e-resize;' +
            'padding:1em 0px;' +
            'margin-right:0px;' +
            'cursor:col-resize;' +
            '"', '', {isWidthResizeBtn: true, headerCell: headerTextCell});
        headerTextCell.addNodes([headerDiv.addNodes([
            headerButtonDiv,
            new Emt('div', 'style="flex:1;"').addNodes([
                headerTextCell.resizeBox
            ]),
        ])]);


        ([descSortBtn, noSortBtn, ascSortBtn]).forEach((sortBtn) => {
            sortBtn.addEventListener('click', () => {
                if (sortBtn.sortType === false) {
                    delete dataGrid.param.sort[columnConfig.attrKey];
                } else {
                    dataGrid.param.sort[columnConfig.attrKey] = sortBtn.sortType;
                }
                spans.forEach((span) => {
                    if (span.sortType !== undefined) {
                        if (span.sortType === sortBtn.sortType) {
                            span.classList.remove('hidden');
                        } else {
                            span.classList.add('hidden');
                        }
                    }
                })
                dataGrid.api.requestData('sort');
            });
            if (dataGrid.param.sort[columnConfig.attrKey] !== undefined && dataGrid.param.sort[columnConfig.attrKey] === sortBtn.sortType) {
                sortBtn.dispatchEvent(new CustomEvent('click'));
            }
        })


        //开始attr.filter
        columnConfig.initedInputs = [];
        if (columnConfig.filter.inputs !== false) {
            //  console.log(columnConfig);
            columnConfig.filter.inputs.forEach((columnInput, columnInputIndex) => {
                /// filterCell.addNodes([filterInput]);
                let filterInput = false;
                if (columnInputIndex === 0) {
                    if (columnInput === false) {
                        return false;
                    }
                    if (columnConfig.filter.config.valueItems.length === 0) {
                        filterInput = new Emt('input', 'type="text"');
                        filterInput.addEventListener('keyup', (e) => {
                            //  console.log(e.keyCode, e.key);//8 'Backspace'    27:Escape
                            if (e.keyCode === 27) {
                                filterInput.value = '#';
                                // filterInput.fireEvent('change');
                                //  filterInput.onchange();
                                filterInput.dispatchEvent(new CustomEvent('change'));
                            }
                        });
                    } else {
                        filterInput = new Emt('select', '');
                    }
                    filterInput.setVal = (val) => {
                        filterInput.value = val;
                    }
                    filterInput.getVal = () => {
                        return filterInput.value;
                    }
                    filterInput.setOnChange = (fun) => {
                        filterInput.addEventListener('change', () => {
                            //console.log('xxxx');
                            fun(filterInput);
                        });
                    }
                    filterInput.setItems = (items) => {
                        items.forEach((item) => {
                            //   console.log(item);
                            filterInput.add(new Option(item.text, item.val));
                        })
                    }
                    // console.log(columnConfig.filter.config.valueItems.length, filterInput);
                    filterInput.setAttrsByStr('style="max-width:90%;min-width:1em;"');
                    filterCell.addNodes([
                        new Emt('div', 'style="overflow:hidden;"').addNodes([filterInput])
                    ]);
                    headerTextCell.resizeDivs.push(filterCell.firstElementChild);
                } else {
                    filterInput = columnInput;
                }
                if (columnConfig.filter.config.valueItems.length > 0 && typeof filterInput.setItems === 'function') {
                    filterInput.setItems(([{text: '不选', val: '#'}]).concat(columnConfig.filter.config.valueItems));
                }
                columnConfig.initedInputs.push(filterInput);
            });
        }

        columnConfig.initedInputs.forEach((initedInput) => {
            initedInput.setOnChange(() => {
                let val = initedInput.getVal();
                console.log(initedInput, val);

                if (val === '#') {
                    delete dataGrid.param.attr[columnConfig.attrKey];
                } else {
                    dataGrid.param.attr[columnConfig.attrKey] = val;
                }
                columnConfig.initedInputs.forEach((initedInput2) => {
                    if (initedInput !== initedInput2) {
                        initedInput2.setVal(val);
                    }
                });
                // console.log(dataGrid.param);
            });
        });

    });

    dataGrid.api.downloadRawData = () => {
        let start_date = (new Date()).toLocaleDateString().replace(/\//g, '-') + ' ' + (new Date()).toLocaleTimeString();
        let a = new Emt('a');
        let blob = new Blob(['[' + dataGrid.processData.rawDataJsonStrs.join(",\n") + ']'], {type: "application/json"});

        let reader = new FileReader();
        reader.readAsDataURL(blob); // converts the blob to base64 and calls onload

        a.textContent = "文件正在准备";
        let sec = 0;
        let si = window.setInterval(() => {
            a.textContent = "文件正在准备:" + sec.toString() + 's';
            sec++;
        }, 1000);
        a.download = `${start_date} 原始数据.json`;

        reader.onload = function () {
            a.href = reader.result; // data url
            window.clearInterval(si);
            a.textContent = `${start_date} 原始数据`;
            // downloadLinksDiv.addNode(a);
            a.click();
        };
    };

    dataGrid.api.downloadTableData = () => {
        dataGrid.processData.tableDataJsonStrs = [];
        let headerMap = {};
        let tmpAttrInfos = [];
        config.columns.forEach((columnConfig, tmp_i) => {
            let attrKey = columnConfig.attrKey === false ? `col_${tmp_i}` : columnConfig.attrKey;
            headerMap[attrKey] = columnConfig.handleKey;
            tmpAttrInfos.push({k: attrKey, handleKey: columnConfig.handleKey});
        });
        dataGrid.processData.tableDataJsonStrs.push(JSON.stringify(headerMap));

        dataGrid.dataTrs.forEach((dataTr) => {
            let tmpObj = {};
            tmpAttrInfos.forEach((tmpAttrInfo) => {
                tmpObj[tmpAttrInfo.k] = dataTr.apiHandle.td.map[tmpAttrInfo.handleKey].textContent;
            })
            dataGrid.processData.tableDataJsonStrs.push(JSON.stringify(tmpObj));
        });

        let start_date = (new Date()).toLocaleDateString().replace(/\//g, '-') + ' ' + (new Date()).toLocaleTimeString();
        let a = new Emt('a');
        let blob = new Blob(['[' + dataGrid.processData.tableDataJsonStrs.join(",\n") + ']'], {type: "application/json"});

        let reader = new FileReader();
        reader.readAsDataURL(blob); // converts the blob to base64 and calls onload

        a.textContent = "文件正在准备";
        let sec = 0;
        let si = window.setInterval(() => {
            a.textContent = "文件正在准备:" + sec.toString() + 's';
            sec++;
        }, 1000);
        a.download = `${start_date} 展示数据.json`;

        reader.onload = function () {
            a.href = reader.result; // data url
            window.clearInterval(si);
            a.textContent = `${start_date} 展示数据`;
            // downloadLinksDiv.addNode(a);
            a.click();
        };
    };


    dataGrid.ele.requestBtn.addEventListener('click', function () {
        dataGrid.api.requestData('goto');
    })
    dataGrid.ele.toggleTableWidthBtn.addEventListener('click', function () {
        if (dataGrid.ele.toggleTableWidthBtn.textContent === 'table铺开') {
            dataGrid.ele.Table.style.width = 'auto';
            dataGrid.ele.toggleTableWidthBtn.textContent = 'table收拢';
        } else {
            dataGrid.ele.Table.style.width = '100%';
            dataGrid.ele.toggleTableWidthBtn.textContent = 'table铺开';
        }
    });


    //缩放列宽度 ，应该是table本身，等会试试
    // document.addEventListener('mousedown', (event) => {
    dataGrid.ele.Table.addEventListener('mousedown', (event) => {
        if (event.target.isWidthResizeBtn) {
            let resize_box = event.target;
            let start_x = event.x;
            let min_width = resize_box.parentElement.previousElementSibling.firstElementChild.offsetWidth + 10 + event.target.offsetWidth;
            let td_width = resize_box.parentElement.parentElement.parentElement.offsetWidth;

            let mousemove = (e) => {
                let diff = e.x - start_x;
                // let expect = diff + min_width;
                let expect = diff + td_width;
                //console.log({dffi: diff, min_width: min_width, td_width: td_width, expect: expect});
                //  resize_box.parentElement.parentElement.style.width = expect + 'px';

                let new_width_str = '100%';
                if (expect < min_width) {
                    new_width_str = min_width + 'px';
                } else {
                    new_width_str = expect + 'px';
                }
                resize_box.parentElement.parentElement.style.width = new_width_str;
                resize_box.headerCell.resizeDivs.forEach((resizeDiv) => {
                    resizeDiv.style.width = new_width_str;
                });

                // resize_box.parentElement.style.width = ((diff > 0 ? diff : 0) + td_width) + 'px';
            };
            document.addEventListener('mousemove', mousemove);
            document.addEventListener('mouseup', (event2) => {
                document.removeEventListener('mousemove', mousemove);
            });
        }
    });
    return dataGrid;

};



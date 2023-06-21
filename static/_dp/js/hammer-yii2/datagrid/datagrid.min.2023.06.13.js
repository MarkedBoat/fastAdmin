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
            __doc: {
                ele: '将网格 填到哪个地方',
                title: '网格标题',
                requestDataFun: 'fun,里面根据 本handle提供的信息 进行请求 指定数据  ，参数:  1.事件(filter变动事件/点击跳页/点击排序/点击goto按钮)，   2.{filter_data:{},page:{},sort:{}  } 3:回调函数，这个由本handle提供的 dataGrid.api.reloadDataRows，实现代码负责回调，用于更新数据',
                initSearchCondtion: '预设查询条件',
            },
            set: {
                dstDivElement: false,
                requestDataRowsFunction: false,
            },
            api: {
                funs: [],
                // reloadDataRows: false,
                //  requestData: false,
                //getRequestParam: false,
            },
            ele: {
                inputMap: {}
            },
            param: {
                attr: {},
                page: {
                    index: 1,
                    size: 20,
                },
                sort: {id: 'desc'}
            },
            inputParam: {
                attr: {},
                page: {
                    index: 1,
                    size: 20,
                },
                sort: {id: 'desc'}
            },
            column: {
                keys: [],
                renderFunMap: {},
                handle: {map: {}, list: [], unInitList: []},
                sortButton: {map: {}, list: []}
            },
            dataTrs: []
        };
        for (var k in dataGrid.__doc) {
            if (typeof input_param[k] !== 'undefined') {
                dataGrid[k] = input_opt[k];
            }
        }

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
                    data: {},
                    fun: (param, dataGrid) => {
                    },
                },
                page: {//分页
                    data: {
                        page_index: 1,
                        page_size: 20,
                    },
                    fun: (param, dataGrid) => {
                    },
                },
                sort: {//排序
                    data: {},
                    fun: (param, dataGrid) => {
                    },
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
                            //xxxx// 要具体的 html element了，必须要实现 getVal/setVal/setOnchange 方法
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
        } else {
            if (typeof input_param.dataSource.url !== "string") {
                throw 'config.dataSource.url 必须是有效的string';
            }
            config.dataSource.url = input_param.dataSource.url;
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
        } else {
            if (input_param.paramPreset.attr === undefined) {
                console.warn(" config.paramPreset.attr 没有设置");
            } else {
                if (typeof input_param.paramPreset.attr.data === "object") {
                    config.paramPreset.attr.data = input_param.paramPreset.attr.data;
                } else {
                    console.warn(" config.paramPreset.attr.data 没有设置");
                }
                if (typeof input_param.paramPreset.attr.fun === "function") {
                    config.paramPreset.attr.fun = input_param.paramPreset.attr.fun;
                } else {
                    console.warn(" config.paramPreset.attr.fun 没有设置");
                }
            }

            if (input_param.paramPreset.sort === undefined) {
                console.warn(" config.paramPreset.sort 没有设置");
            } else {
                if (typeof input_param.paramPreset.sort.data === "object") {
                    config.paramPreset.sort.data = input_param.paramPreset.sort.data;
                } else {
                    console.warn(" config.paramPreset.sort.data 没有设置");
                }
                if (typeof input_param.paramPreset.sort.fun === "function") {
                    config.paramPreset.sort.fun = input_param.paramPreset.sort.fun;
                } else {
                    console.warn(" config.paramPreset.sort.fun 没有设置");
                }
            }

            if (input_param.paramPreset.page === undefined) {
                console.warn(" config.paramPreset.page 没有设置");
            } else {
                if (typeof input_param.paramPreset.page.data === "object") {
                    config.paramPreset.page.data = input_param.paramPreset.page.data;

                    if (typeof input_param.paramPreset.page.data.page_index === "number" && input_param.paramPreset.page.data.page_index > 0) {
                        config.paramPreset.page.data.page_index = input_param.paramPreset.page.data.page_index;
                    } else {
                        config.paramPreset.page.data.page_index = 1;
                        console.warn(" config.paramPreset.page.data.page_index 没有设置");
                    }
                    if (typeof input_param.paramPreset.page.data.page_size === "number" && input_param.paramPreset.page.data.page_size > 0) {
                        config.paramPreset.page.data.page_size = input_param.paramPreset.page.data.page_size;
                    } else {
                        config.paramPreset.page.data.page_size = 3;
                        console.warn(" config.paramPreset.page.data.page_size 没有设置");
                    }
                } else {
                    console.warn(" config.paramPreset.page.data 没有设置");
                }
                if (typeof input_param.paramPreset.page.fun === "function") {
                    config.paramPreset.page.fun = input_param.paramPreset.page.fun;
                } else {
                    console.warn(" config.paramPreset.page.fun 没有设置");
                }
            }

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
            '"'
        );
        dataGrid.ele.THead = dataGrid.ele.Table.createTHead();
        dataGrid.ele.TBody = dataGrid.ele.Table.createTBody();
        dataGrid.ele.TFoot = dataGrid.ele.Table.createTFoot();
        dataGrid.ele.TCaption = dataGrid.ele.Table.createCaption();


        dataGrid.ele.submitBtn = new Emt('button').setPros({className: 'btn btn-default', textContent: '=>'});

        dataGrid.ele.pagination = new Emt('DIV', '', '');
        dataGrid.ele.pagination.gotoStart = new Emt('span', 'href="#" style="cursor: pointer"', '«', {goto: 1});
        dataGrid.ele.pagination.gotoEnd = new Emt('span', 'href="#" style="cursor: pointer"', '»', {goto: 'end'});
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

        dataGrid.ele.pagination.pageSizeInput = new Emt('input', 'type="number" class="form-control "  style=" display: inline;  "', '', {value: config.paramPreset.page.data.page_size});
        dataGrid.ele.pagination.pageIndexInput = new Emt('input', 'type="number" class="form-control "  style=" display: inline;   "', '', {value: config.paramPreset.page.data.page_index});
        dataGrid.ele.pagination.pageJumpBtn = new Emt('button', 'type="button" class="btn btn-default"', '跳转至');

        dataGrid.ele.pagination.pageBtns.concat([dataGrid.ele.pagination.gotoStart, dataGrid.ele.pagination.gotoEnd]).forEach((pageGotoBtn) => {
            pageGotoBtn.addEventListener('click', function () {
                this.setStyleActive();
                dataGrid.ele.pagination.pageIndexInput.value = this.goto;
                dataGrid.api.jumpPageTo();
            });
        })
        dataGrid.ele.pagination.pageJumpBtn.addEventListener('click', function () {
            dataGrid.api.jumpPageTo();
        });


        dataGrid.ele.pagination.addNodes([
            new Emt('div', 'class="col-xs-6"').addNodes([
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
            new Emt('div', 'class="col-xs-3"').addNodes([
                new Emt('div', 'class="input-group" style="margin:20px 0;"').addNodes([
                    new Emt('span', 'class="input-group-addon"', '每页行数'),
                    dataGrid.ele.pagination.pageSizeInput
                ])
            ]),
            new Emt('div', 'class="col-xs-2"').addNodes([
                new Emt('div', 'class="input-group" style="margin:20px 0;"').addNodes([
                    dataGrid.ele.pagination.pageIndexInput,
                    new Emt('span', 'class="input-group-btn"').addNodes([
                        dataGrid.ele.pagination.pageJumpBtn
                    ])
                ])
            ]),
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


        dataGrid.topTr.addTd('goto_submit').addNodes([
            dataGrid.ele.submitBtn,
        ]);

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
            dataGrid.column.handle.unInitList.forEach((columnHandle) => {
                columnHandle.initColumn();
            })

            config.container.appendChild(
                new Emt('div', 'class="table-responsive" ').addNodes([
                    dataGrid.ele.Table,
                    dataGrid.ele.pagination
                ])
            );
            return dataGrid;
        }

        /**
         * 对外开放 ，获取 页面参数
         * @returns {{filter: {}, sort: {type, key: *}, page: {size, index}}}
         */
        dataGrid.api.getRequestParam = () => {
            let requestParam = {
                sort: {},
                page: {size: dataGrid.ele.pagination.pageSizeInput.value, index: dataGrid.ele.pagination.pageIndexInput.value},
                filter: {}
            };
            dataGrid.column.keys.forEach(function (column_key) {
                let tmp_val = dataGrid.column.handle.map[column_key].getFilterInputValue();
                if (tmp_val === undefined || tmp_val === '#') return false;
                requestParam.filter[column_key] = tmp_val;
            });


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

        dataGrid.ele.submitBtn.addEventListener('click', function () {
            dataGrid.api.requestData('goto');
        })


        /**
         * filterInputElement/filter_ele 必须是本类创建的，不然有些东西怕是监听不到，但是有些列的filter是没必要的，填string 也可以是的
         * -- filterInputPlacehoderString 占位符
         * title/label 就是 column中文名
         * column_name/column_key 行数据的 列 字段, 用来存放操作按钮的列，也得起个名字
         * isSortable/is_sortable true:可怕徐
         * cellRenderFunction/data_cell_function(td_cell,row_data) 针对数据行的，操作行还有其他东西进行填充，但是数据行就得调相应得方法了，必须接受 单元格(cell) 和 行数据(row_data)作为参数
         */
        dataGrid.api.preCreateColumn = function (columnKey) {
            let columnHandle = {
                columnKey: columnKey,
                isSortable: false,
                headerText: false,
                columnInfo: false,
                freeFilterInput: false,//独立自定义的 过滤输入, free 优先级 高于 fixed, free存在的情况下 fixed 为 readonly
                headerFilterInput: false,//固定的 过滤输入
                headerFilterInputConfig: {
                    inputType: 'text',
                    valueItems: [],
                },
                renderFun: () => {
                    alert('renderFunction 未初始化:' + columnKey);
                },
                hasRenderFunction: false,
                sortButton: false,
            };
            columnHandle.setHeaderText = (text) => {
                columnHandle.headerText = text;
                return columnHandle;
            };
            columnHandle.setSortable = (isSortable) => {
                columnHandle.isSortable = isSortable;
                return columnHandle;
            };
            columnHandle.setColumnInfo = (columnInfo) => {
                columnHandle.columnInfo = columnInfo;
                return columnHandle;
            };

            columnHandle.bindFreeFilterInput = (freeFilterInput) => {
                columnHandle.freeFilterInput = freeFilterInput;
                return columnHandle;
            };
            columnHandle.setHeaderFilterInputConfig = (headerFilterInputConfig) => {
                // columnHandle.headerFilterInput.inputType = headerFilterInputConfig.inputType || 'text';
                if (headerFilterInputConfig.valueItems.length === 0) {
                    columnHandle.headerFilterInput = new Emt('input', 'type="text"');
                } else {
                    columnHandle.headerFilterInput = new Emt('select');
                    columnHandle.headerFilterInput.add(new Option('不选', '#'));
                    headerFilterInputConfig.valueItems.forEach((item) => {
                        columnHandle.headerFilterInput.add(new Option(item.text, item.val));
                    })
                }
                columnHandle.headerFilterInput.valueItems = headerFilterInputConfig.valueItems || [];

                return columnHandle;
            };


            columnHandle.setRenderFunction = (fun) => {
                columnHandle.renderFun = fun;
                columnHandle.hasRenderFunction = true;
                return columnHandle;
            };

            columnHandle.getFilterInputValue = () => {
                // console.log(columnHandle, columnHandle.freeFilterInput, columnHandle.headerFilterInput);
                if (columnHandle.freeFilterInput === false) {
                    if (columnHandle.headerFilterInput === false) {
                        return undefined;
                    } else {
                        return columnHandle.headerFilterInput.value;
                    }
                } else {
                    return columnHandle.freeFilterInput.apiHandle.getVal();
                }
            };

            columnHandle.initColumn = () => {
                if (columnHandle.isInited === undefined) {
                    columnHandle.isInited = true;
                } else {
                    throw  '已经初始化了';
                }
                if (dataGrid.column.keys.indexOf(columnHandle.columnKey) !== -1) {
                    throw  'columnKey 已经初始化了:' + columnHandle.columnKey;
                }

                //排序按钮
                if (columnHandle.isSortable === true) {
                    columnHandle.sortButton = dataGrid.createSortButton(columnHandle.headerText, columnHandle.columnKey);
                    dataGrid.column.sortButton.list.push(columnHandle.sortButton);
                    dataGrid.column.sortButton.map[columnHandle.columnKey] = columnHandle.sortButton;
                } else {
                    columnHandle.sortButton = new Emt('span', 'class="btn-default btn-xs"', columnHandle.headerText);
                }
                if (columnHandle.columnInfo && columnHandle.columnInfo.remark) {
                    columnHandle.sortButton.setAttribute("title", columnHandle.columnInfo.remark);
                }

                let resize_box = new Emt('button', 'style="' +
                    'float:right;' +
                    'min-height:100%;' +
                    'min-width:1px;' +
                    'cursor: e-resize;' +
                    'padding:1em 0px;' +
                    'margin-right:0px;' +
                    'cursor:col-resize;' +
                    '"', '', {isWidthResizeBtn: true});
                let titleTd = dataGrid.titleTr.addTd(columnHandle.columnKey);
                titleTd.addNodes([
                    new Emt('div', 'style="' +
                        // 'float:left;' +
                        'overflow-x:auto;' +
                        'display: flex;' +
                        'width:100%;' +
                        'height:2.5em;' +
                        '"').addNodes([
                        new Emt('div', 'style="overflow:hidden;"').addNodes([
                            columnHandle.sortButton
                        ]),
                        new Emt('div', 'style="flex:1;"').addNodes([
                            resize_box
                        ]),


                    ])
                ]);


                if (columnHandle.headerFilterInput === false) {
                    dataGrid.filterTr.addTd(columnHandle.columnKey).textContent = columnHandle.filterInputPlacehoderString || '';
                } else {
                    columnHandle.headerFilterInput.classList.add('col-md-12');
                    columnHandle.headerFilterInput.setStyle({width: '100%', minHeight: '1em'});
                    dataGrid.filterTr.addTd(columnHandle.columnKey).addNodes([columnHandle.headerFilterInput]);
                    columnHandle.headerFilterInput.addEventListener('change', function () {
                        if (columnHandle.freeFilterInput !== false) {
                            columnHandle.freeFilterInput.apiHandle.setChangedVal(columnHandle.headerFilterInput.value);
                        }
                        dataGrid.api.requestData('filter');
                    })
                }
                if (columnHandle.freeFilterInput !== false) {
                    columnHandle.freeFilterInput.addEventListener('change', function () {
                        console.log('columnHandle.freeFilterInput');
                        if (columnHandle.headerFilterInput !== false) {
                            columnHandle.headerFilterInput.value = columnHandle.freeFilterInput.apiHandle.getVal();
                        }
                        dataGrid.api.requestData('filter');
                    });
                }


                //渲染cell/单元格 方法

                if (columnHandle.hasRenderFunction) {
                    dataGrid.column.renderFunMap[columnHandle.columnKey] = columnHandle.renderFun;
                } else {
                    dataGrid.column.renderFunMap[columnHandle.columnKey] = false;
                }


                //预置搜索条件
                if (dataGrid.initSearchCondtion && dataGrid.initSearchCondtion.attrs && dataGrid.initSearchCondtion.attrs[columnHandle.columnKey] !== undefined && dataGrid.initSearchCondtion.attrs[columnHandle.columnKey].length > 0) {
                    if (columnHandle.freeFilterInput !== false) {
                        columnHandle.freeFilterInput.setVal(dataGrid.initSearchCondtion.attrs[columnHandle.columnKey]);
                    }
                    if (columnHandle.headerFilterInput !== false) {
                        columnHandle.headerFilterInput.value = dataGrid.initSearchCondtion.attrs[columnHandle.columnKey];
                    }
                }


                dataGrid.column.handle.list.push(columnHandle);
                dataGrid.column.handle.map[columnHandle.columnKey] = columnHandle;
                dataGrid.column.keys.push(columnHandle.columnKey);


                return columnHandle;

            }
            dataGrid.column.handle.unInitList.push(columnHandle);
            return columnHandle;
        };

        dataGrid.createSortButton = (buttonText, columnDataKey) => {
            let sortButton = new Emt('BUTTON', 'type="button" class="btn-default btn-xs hide_btn_sort" style="white-space: nowrap"', buttonText);
            sortButton.addNodes([
                new Emt('SPAN', 'class="glyphicon glyphicon-arrow-up btn_sort_asc"', ''),
                new Emt('SPAN', 'class="glyphicon glyphicon-arrow-down btn_sort_desc"', '')
            ]);
            sortButton.columnKey = columnDataKey;
            sortButton.sortType = false;

            sortButton.setSort = function (type) {
                if (type === 'asc') {
                    sortButton.sortType = 'asc';
                    sortButton.classList.remove('hide_btn_sort_asc');
                    sortButton.classList.add('hide_btn_sort_desc');
                    sortButton.classList.remove('hide_btn_sort');

                    sortKey_hideInput.value = sortButton.columnKey;
                    sortType_hideInput.value = sortButton.sortType;
                } else if (type === 'desc') {
                    sortButton.sortType = 'desc';
                    sortButton.classList.add('hide_btn_sort_asc');
                    sortButton.classList.remove('hide_btn_sort_desc');
                    sortButton.classList.remove('hide_btn_sort');

                    sortKey_hideInput.value = sortButton.columnKey;
                    sortType_hideInput.value = sortButton.sortType;
                } else {
                    sortButton.sortType = false;
                    sortButton.classList.remove('hide_btn_sort_asc');
                    sortButton.classList.remove('hide_btn_sort_desc');
                    sortButton.classList.add('hide_btn_sort');

                    sortKey_hideInput.value = sortButton.columnKey;
                    sortType_hideInput.value = sortButton.sortType;
                }
            }


            sortButton.addEventListener('click', function () {
                dataGrid.column.sortButton.list.forEach(function (tmp_btn) {
                    if (tmp_btn.columnKey !== sortButton.columnKey) {
                        tmp_btn.setSort(false);
                    }
                });
                if (sortButton.sortType === 'asc') {
                    sortButton.setSort('desc');
                } else {
                    sortButton.setSort('asc');
                }
                sortKey_hideInput.value = sortButton.columnKey;
                sortType_hideInput.value = sortButton.sortType;
                dataGrid.resortFun(this);
            });


            return sortButton;
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
        }
        dataGrid.flushPager = function (pageTotal, pageIndex) {
            dataGrid.ele.pagination.gotoEnd.goto = pageTotal;
            console.log(pageTotal, pageIndex);
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
            console.log(pageIndex, tmpPageNums, pageNums);
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
            data.dataRows.forEach(function (row_data) {
                dataGrid.addDataRow(row_data);
            });
            dataGrid.topTr.apiHandle.td.map.goto_submit.setAttribute('colspan', dataGrid.column.keys.length);
            dataGrid.footTr.apiHandle.td.map.page.setAttribute('colspan', dataGrid.column.keys.length);
            dataGrid.flushPager(data.pageTotal, data.pageIndex);
        }

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
                    console.log({dffi: diff, min_width: min_width, td_width: td_width, expect: expect});
                    //  resize_box.parentElement.parentElement.style.width = expect + 'px';

                    if (expect < min_width) {
                        resize_box.parentElement.parentElement.style.width = min_width + 'px';
                    } else {
                        resize_box.parentElement.parentElement.style.width = expect + 'px';
                    }

                    // resize_box.parentElement.style.width = ((diff > 0 ? diff : 0) + td_width) + 'px';
                };
                document.addEventListener('mousemove', mousemove);
                document.addEventListener('mouseup', (event2) => {
                    document.removeEventListener('mousemove', mousemove);
                });
            }
        });


        dataGrid.api.requestData = function (event_type) {
            console.log('event_type', event_type);

            let page_data = dataGrid.api.getRequestParam();
            console.log('\nsetRequestDataRowsFunction:<<<\n', 'event_type', event_type, 'post_data', page_data, '\n>> setRequestDataRowsFunction\n');
            if (event_type === 'filter') {
                console.log('这个不做理会，不然请求太频繁了');
                return false;
            }
            let post_data = {
                page_index: page_data.page.index,
                page_size: page_data.page.size,
                attr: page_data.filter,
            };
            for (let key in config.dataSource.param.append) {
                post_data[key] = config.dataSource.param.append[key];
            }
            if (config.dataSource.param.fun !== false) {
                post_data = config.dataSource.param.fun(post_data);
            }
            // post_data.attr.table_name = window.serverData.table_name;

            if (!page_data.sort.key || !page_data.sort.type) {
                console.log('没有排序');
            } else {
                // post_data.sort = page_data.sort;
                post_data.sort = {};
                post_data.sort[page_data.sort.key] = page_data.sort.type;
            }

            for (let attr_key in post_data.attr) {
                console.log(attr_key);
                if (post_data.attr[attr_key][0] === ':') {
                    post_data.attr[attr_key] = 'like:%' + post_data.attr[attr_key].substring(1) + '%';
                }
            }


            kl.ajax({
                url: '/_dp/v1/dbdata/select',
                data: post_data,
                type: 'json',
                success: function (res_request_data) {
                    console.log('请求成功');
                    if (res_request_data.status) {
                        if (res_request_data.status === 200) {
                            //handle_callback(res_request_data.data.dataRows);

                            //     select_page_info_span.textContent = '当前页码' + res_request_data.data.pageIndex + '/' + res_request_data.data.pageTotal + '.        .';
                            //   select_count_info_span.textContent = ' 共' + res_request_data.data.rowsTotal + '条';
                            dataGrid.api.reloadDataRows(res_request_data.data);
                        } else {
                            alert('错误:' + (res_request_data.msg || '未知'))
                        }
                    } else {
                        console.log(res_request_data.status);
                        alert('请求结果异常')
                    }
                },
                error: function (res_request_data) {
                    console.log('datagrid.api.setRequestDataRowsFunction 网络异常:' + res_request_data);
                    alert('网络异常');
                }
            });
        };


        config.columns.forEach((columnConfig, columnConfigIndex) => {
            let descSortBtn = new Emt('button', 'type="button" role="menuitem" tabindex="-1" href="#"', '倒叙排列');
            let ascSortBtn = new Emt('button', 'type="button" role="menuitem" tabindex="-1" href="#"', '倒叙排列');
            let noSortBtn = new Emt('button', 'type="button" role="menuitem" tabindex="-1" href="#"', '倒叙排列');
            let headerDiv = new Emt('DIV', 'class="dropdown"').addNodes([
                new Emt('BUTTON', `type="button" class="btn btn-info dropdown-toggle" id="headertext_${columnConfig.handleKey}" data-toggle="dropdown"`, columnConfig.headerText).addNodes([
                    new Emt('SPAN', 'class="caret"')
                ]),
                new Emt('UL', `class="dropdown-menu" role="menu" aria-labelledby="headertext_${columnConfig.handleKey}"`).addNodes([
                    new Emt('LI', 'role="presentation"').addNodes([descSortBtn]),
                    new Emt('LI', 'role="presentation"').addNodes([noSortBtn]),
                    new Emt('LI', 'role="presentation"').addNodes([ascSortBtn])
                ])
            ]);
            let headerTextCell = dataGrid.titleTr.addTd(columnConfig.handleKey);
            headerTextCell.addNodes([headerDiv]);


            let filterInput = new Emt('input', 'type="text"');
            let filterCell = dataGrid.filterTr.addTd(columnConfig.handleKey);
            filterCell.addNodes([filterInput]);

        });

        return dataGrid;

    }
;



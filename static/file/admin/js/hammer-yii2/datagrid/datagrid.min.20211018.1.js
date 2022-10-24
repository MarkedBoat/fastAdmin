/**
 *
 * @param input_opt  {
 * csrf:'string',
 * title:'title',
 * }
 * @returns {{hide: hide, show: show}}
 */
let hammerBootstarpDatagrid = function (input_opt) {
    let handle_this = {
        __doc: {
            ele: '将网格 填到哪个地方',
            title: '网格标题',
            requestDataFun: 'fun,里面根据 本handle提供的信息 进行请求 指定数据  ，参数:  1.事件(filter变动事件/点击跳页/点击排序/点击goto按钮)，   2.{filter_data:{},page:{},sort:{}  } 3:回调函数，这个由本handle提供的 handle_this.reloadDataRows，实现代码负责回调，用于更新数据',
        }
    };
    for (var k in handle_this.__doc) {
        if (typeof input_opt[k] !== 'undefined') {
            handle_this[k] = input_opt[k];
        }
    }


    handle_this.root_table_ele = new Emt('table').setAttrsByStr('class="table table-bordered table-striped table-hover"');
    handle_this.root_table_ele.thead = handle_this.root_table_ele.createTHead();
    handle_this.root_table_ele.tbody = handle_this.root_table_ele.createTBody();
    handle_this.root_table_ele.tfoot = handle_this.root_table_ele.createTFoot();
    handle_this.root_table_ele.tcaption = handle_this.root_table_ele.createCaption();

    handle_this.filter_eles = {keys: [], ele_map: {}, btns: []};


    handle_this.addTr = function (table_tpart, opt) {
        let last_tr = table_tpart.insertRow();
        if (typeof opt === 'object') {
            last_tr.setAttrs(opt);
        }
        last_tr.addTd = function (cell_key, opt2) {
            let last_td = last_tr.insertCell();
            last_tr['cell_' + cell_key] = last_td;
            last_td.setPros = function (configs) {
                for (var attr in configs)
                    last_td[attr] = configs[attr];
                return last_td;
            };

            if (typeof opt === 'object') {
                last_td.setPros(opt2);
            }

            last_td.addNode = function () {
                for (var i = 0; i < arguments.length; i++) {
                    if (typeof arguments[i] !== 'string') {
                        last_td.appendChild(arguments[i]);
                        if (typeof arguments[i + 1] === 'string') {
                            if (arguments[i + 1]) self[arguments[i + 1]] = arguments[i];
                        }
                    }
                }
                return last_td;
            };
            last_td.addNodes = function (nodes) {
                for (var i in nodes) {
                    var node = nodes[i];
                    if (typeof node === 'string') {
                        last_td.innerHTML += node;
                    } else {
                        last_td.appendChild(node);
                    }
                }
                return last_td;
            };

            return last_td;
        }
        return last_tr;
    }

    handle_this.tr_top = handle_this.addTr(handle_this.root_table_ele.thead);
    handle_this.tr_title = handle_this.addTr(handle_this.root_table_ele.thead);
    handle_this.tr_filter = handle_this.addTr(handle_this.root_table_ele.thead);


    let input_hide_sort_column_key = new Emt('input').setPros({type: 'hidden'});
    let input_hide_sort_type = new Emt('input').setPros({type: 'hidden'});
    let input_hide_page_index = new Emt('input').setPros({type: 'hidden'});
    let input_hide_page_size = new Emt('input').setPros({type: 'hidden'});


    let btn_submit = new Emt('button').setPros({textContent: '=>'});
    handle_this.tr_top.addTd('btn_go').addNodes([
        btn_submit,
        input_hide_sort_column_key,
        input_hide_sort_type,
        input_hide_page_index,
        input_hide_page_size
    ]);


    handle_this.requestData = function (event_type) {
        let data = {
            sort: {key: input_hide_sort_column_key.value, type: input_hide_sort_type.value},
            page: {size: input_hide_page_size.value, index: input_hide_page_index.value},
            filter: {}
        };
        handle_this.filter_eles.keys.forEach(function (column_key) {
            data.filter[column_key] = handle_this.filter_eles.ele_map[column_key].getFilterValue();
        })
        console.log('event_type', event_type, 'data', data);
        handle_this.requestDataFun(event_type, data, handle_this.reloadDataRows);
    }
    handle_this.gotPage = function () {
        handle_this.requestData('page');
    }
    handle_this.resortFun = function (btn_ele) {
        handle_this.requestData('sort');
    }
    handle_this.filterChange = function (btn_ele) {
        handle_this.requestData('filter');
    }

    btn_submit.addEventListener('click', function () {
        handle_this.requestData('goto');
    })

    handle_this.createSpan = function (opt) {
        return new Emt('span').setAttrsByStr('');
    }
    handle_this.createInputText = function (opt) {
        let tmp_input = new Emt('input').setAttrsByStr('type="text" ');
        tmp_input.getFilterValue = function () {
            return tmp_input.value;//根据不同opt 做对应的转化
        }
        tmp_input.addEventListener('change', function () {
            handle_this.requestData('filter');
        })
        return tmp_input;
    }


    handle_this.createInputSelect = function (opt, ele_key) {
        let select_ele = new Emt('select').setAttrsByStr('type="text" ');
        select_ele.addItem = function (label, val) {
            select_ele.add(new Option(label, val));
        }
        select_ele.addItem('不选择', '___no_select');
        if (opt && opt.list && typeof opt.list.forEach === 'function') {
            opt.list.forEach(function (item) {
                select_ele.addItem(item.label, item.val);
            })
        }
        return select_ele;
    }


    handle_this.data_cell = {keys: [], fun_map: {}};


    /**
     * filter_ele 必须是本类创建的，不然有些东西怕是监听不到，但是有些列的filter是没必要的，填string 也可以是的
     * label 就是 column中文名
     * column_key 行数据的 列 字段, 用来存放操作按钮的列，也得起个名字
     * is_sortable true:可排序
     * data_cell_function(td_cell,row_data) 针对数据行的，操作行还有其他东西进行填充，但是数据行就得调相应得方法了，必须接受 单元格(cell) 和 行数据(row_data)作为参数
     */
    handle_this.addHeader = function (filter_ele, column_label, column_key, is_sortable, data_cell_function, column_info) {
        console.log('filter:', filter_ele, 'label:', column_label, 'column_key:', column_key, 'is_sortable:', is_sortable, 'data_cell_function', data_cell_function);
        //hide_btn_sort  hide_sort_asc hide_sort_desc
        let btn_header;
        if (is_sortable === true) {
            btn_header = new Emt('BUTTON').setAttrsByStr('type="button" class="btn-default btn-xs hide_btn_sort"', column_label);
            btn_header.addNodes([
                new Emt('SPAN').setAttrsByStr('class="glyphicon glyphicon-arrow-up btn_sort_asc"', ''),
                new Emt('SPAN').setAttrsByStr('class="glyphicon glyphicon-arrow-down btn_sort_desc"', '')
            ]);
            btn_header.sort_info = {type: false};
            btn_header.clearSort = function () {
                btn_header.sort_info.type = false;
                btn_header.classList.remove('hide_btn_sort_asc');
                btn_header.classList.remove('hide_btn_sort_desc');
                btn_header.classList.add('hide_btn_sort');
            }
            btn_header.setSortAsc = function () {
                btn_header.sort_info.type = 'asc';
                btn_header.classList.remove('hide_btn_sort_asc');
                btn_header.classList.add('hide_btn_sort_desc');
                btn_header.classList.remove('hide_btn_sort');
            }
            btn_header.setSortDesc = function () {
                btn_header.sort_info.type = 'desc';
                btn_header.classList.add('hide_btn_sort_asc');
                btn_header.classList.remove('hide_btn_sort_desc');
                btn_header.classList.remove('hide_btn_sort');
            }

            handle_this.filter_eles.btns.push(btn_header);
            btn_header.addEventListener('click', function () {
                handle_this.filter_eles.btns.forEach(function (tmp_btn) {
                    if (tmp_btn === btn_header) return false;
                    tmp_btn.clearSort();
                });
                if (btn_header.sort_info.type === 'asc') {
                    btn_header.setSortDesc();
                } else {
                    btn_header.setSortAsc();
                }
                input_hide_sort_column_key.value = column_key;
                input_hide_sort_type.value = btn_header.sort_info.type;
                handle_this.resortFun(this);
            });
        } else {
            btn_header = new Emt('span').setAttrsByStr('class="btn-default btn-xs"', column_label);
        }
        if (column_info && column_info.remark) {
            btn_header.setAttribute("title", column_info.remark);
        }
        handle_this.tr_title.addTd(column_key).addNodes([btn_header]);
        if (typeof filter_ele === 'string') {
            handle_this.tr_filter.addTd(column_key).textContent = filter_ele;
        } else {
            filter_ele.classList.add('col-md-12');
            handle_this.tr_filter.addTd(column_key).addNodes([filter_ele]);
        }
        handle_this.data_cell.keys.push(column_key);
        if (typeof data_cell_function !== 'undefined') {
            if (typeof data_cell_function === 'function') {
                handle_this.data_cell.fun_map[column_key] = data_cell_function;
            } else {
                throw  'data_cell_function 不是函数,要么给function,要么不给';
            }
        }

        if (typeof filter_ele.getFilterValue === 'function') {
            handle_this.filter_eles.keys.push(column_key);
            handle_this.filter_eles.ele_map[column_key] = filter_ele;
        }
        return handle_this;
    }

    handle_this.data_trs = [];
    handle_this.addDataRow = function (row_data) {
        let data_tr = handle_this.addTr(handle_this.root_table_ele.tbody);
        handle_this.data_trs.push(data_tr);
        data_tr.data_info = row_data;
        handle_this.data_cell.keys.forEach(function (column_key) {
            let data_cell = data_tr.addTd(column_key);
            if (typeof row_data[column_key] === 'undefined') {
                if (typeof handle_this.data_cell.fun_map[column_key] !== 'function') {
                    console.log(row_data, column_key);
                    throw column_key + ',row_data没有这个字段，也没有设置处理function';
                }
                handle_this.data_cell.fun_map[column_key](data_cell, row_data);
            } else {
                if (typeof handle_this.data_cell.fun_map[column_key] === 'function') {
                    handle_this.data_cell.fun_map[column_key](data_cell, row_data);
                } else {
                    data_cell.innerText = row_data[column_key] === null ? '' : row_data[column_key].toString();
                }
            }
        })
    }
    handle_this.clearDataRows = function () {
        let max = handle_this.data_trs.length;
        for (let i = 0; i < max; i++) {
            console.log(handle_this.data_trs.length);
            handle_this.data_trs[i].remove();
        }
        handle_this.data_trs = [];
    }

    /**
     * 重新载入数据
     * @param row_datas
     */
    handle_this.reloadDataRows = function (row_datas) {
        handle_this.clearDataRows();
        row_datas.forEach(function (row_data) {
            handle_this.addDataRow(row_data);
        });
    }


    if (input_opt.ele) {
        input_opt.ele.appendChild(new Emt('div').setAttrsByStr('class="table-responsive" ').addNodes([handle_this.root_table_ele]));
    }
    return handle_this;

};


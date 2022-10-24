let hammerYii2Bootstarp = function () {

    let getEleRandId = function (prefix) {
        return prefix + '_' + (parseInt(Math.random(1, 2) * 10000000000).toString());
    }
    /**
     *
     * @link https://www.runoob.com/bootstrap/bootstrap-modal-plugin.html  Bootstrap 模态框（Modal）插件
     * @param input_option
     * @returns {{title_ele, body_ele, hide: hide, show: show}}
     */
    let hammerYii2Bootstarp_Modal = function (input_option) {
        let input_opt = input_option || {};
        let div_modal = new Emt('div').setAttrsByStr('class="fade modal" role="dialog" tabindex="-1"').setPros({id: getEleRandId('modal')});
        let handle = {
            root_ele: div_modal,
            show: function () {
                $('#' + div_modal.id).modal('show');
            },
            hide: function () {
                $('#' + div_modal.id).modal('hide');
            },
        };

        let modal_title = new Emt('h4').setPros({textContent: input_opt.title || '标题'}).setAttrsByStr('class="modal-title"');
        let modal_body = new Emt('div').setAttrsByStr('class="col-lg-12"');
        let modal_footer = new Emt('div').setAttrsByStr('class="modal-foote"');

        div_modal.addNodes([
            new Emt('div').setAttrsByStr('class="modal-dialog"').addNodes([
                new Emt('div').setAttrsByStr('class="modal-content"').addNodes([
                    //头部
                    new Emt('div').setAttrsByStr('class="modal-header"').addNodes([
                        new Emt('button').setPros({textContent: 'x'}).setAttrsByStr('type="button" class="close" data-dismiss="modal" aria-hidden="true"'),
                        modal_title
                    ]),
                    //主体
                    new Emt('div').setAttrsByStr('class="modal-body"').addNodes([
                        new Emt('div').setAttrsByStr('class="row"').addNodes([
                            modal_body
                        ])
                    ]),
                    modal_footer
                ])
            ])
        ]);

        document.body.append(new Emt('div').addNodes([div_modal]));

        handle.setZindex = function (zindex_num) {
            div_modal.style.zIndex = zindex_num;
            return handle;
        }
        handle.title_ele = modal_title;
        handle.body_ele = modal_body;
        handle.foot_ele = modal_footer;

        modal_footer

        return handle;

    };
    /**
     * @link https://www.runoob.com/bootstrap/bootstrap-panels.html Bootstrap 面板（Panels）

     * @returns {{title_ele, body_ele, root}}
     */
    let hammerYii2Bootstarp_Panel = function (input_option) {
        let input_opt = input_option || {};
        let title = new Emt('H3').setAttrsByStr('class="panel-title"', input_option.title || '面板标题');
        let body = new Emt('DIV').setAttrsByStr('class="panel-body"', input_option.detail || '这是一个基本的面板');
        let footer = new Emt('DIV').setAttrsByStr('class="panel-footer"', input_option.footer || '面板脚注');
        let root_ele = new Emt('DIV').setAttrsByStr('class="panel panel-primary"', '').addNodes([
            new Emt('DIV').setAttrsByStr('class="panel-heading"', '').addNodes([
                title
            ]),
            body,
            footer
        ]);
        let handle = {
            root_ele: root_ele,
            title_ele: title,
            body_ele: body,
            footer_ele: footer,
        };

        let opts = {color: {items: ['primary', 'success', 'info', 'warning', 'danger']}};
        //设置不同颜色
        opts.color.set = function (color_key) {
            if (color_key !== '') {
                root_ele.classList.add('panel-' + color_key);
            }
            opts.color.items.forEach(function (color) {
                if (color_key !== color) {
                    root_ele.classList.remove('panel-' + color);
                }
            })
            return handle;
        };
        handle.getRootEle = function () {
            return root_ele;
        }
        handle.opts = opts;
        root_ele.handle = handle;
        return handle;
    }


    let hammerYii2Bootstarp_Button = function (input_option) {
        let input_opt = input_option || {};
        let root_ele = new Emt(input_opt.tagName || 'button').setAttrsByStr('class="btn btn-default" ', input_option.title || '按钮文字');
        let handle = {
            root_ele: root_ele,
        };
        let opts = {
            color: {items: ['primary', 'success', 'info', 'warning', 'danger', 'link', 'default']},
            size: {items: ['lg', 'sm', 'xs', 'block']}
        };
        //设置不同颜色
        opts.color.set = function (color_key) {
            if (color_key !== '') {
                root_ele.classList.add('btn-' + color_key);
            }
            root_ele.classList.add('btn-' + color_key);
            opts.color.items.forEach(function (color) {
                if (color_key !== color) {
                    root_ele.classList.remove('btn-' + color);
                }
            });
            return handle;
        };
        //设置不同大小
        opts.size.set = function (size_key) {
            if (size_key !== '') {
                root_ele.classList.add('btn-' + size_key);
            }
            root_ele.classList.add('btn-' + size_key);
            opts.size.items.forEach(function (size) {
                if (size_key !== size) {
                    root_ele.classList.remove('btn-' + size_key);
                }
            })
            return handle;
        };
        handle.getRootEle = function () {
            return root_ele;
        }
        handle.opts = opts;
        root_ele.handle = handle;
        return handle;
    }


    let hammerYii2Bootstarp_Form = function (input_option) {
        let input_opt = input_option || {name_tpl: '$name_key'};
        let id_root_ele = getEleRandId('form');
        let root_ele = new Emt('FORM').setAttrsByStr('class="form-horizontal" role="form"', '').addNodes([]);

        let handle = {
            root_ele: root_ele,
        };

        handle.createInputGroup = function (label, ele_input, handle_ele_key) {
            console.log('handle_ele_key', handle_ele_key);

            if (typeof handle_ele_key === 'string') {
                handle[handle_ele_key] = ele_input;
            }
            return new Emt('DIV').setAttrsByStr('class="form-group"', '').addNodes([
                new Emt('LABEL').setAttrsByStr('for="' + ele_input.id + '" class="col-sm-2 control-label"', label),
                new Emt('DIV').setAttrsByStr('class="col-sm-10"', '').addNodes([
                    ele_input
                ])
            ])
        };


        /**
         * 创造一个空拍的 输入group
         * @param input_param
         * @returns {*}
         */
        handle.createBlankGroup = function (input_param) {
            let blank_div = new Emt('DIV', 'class="col-sm-10"', '');
            let group_div = new Emt('DIV', 'class="form-group"', '').addNodes([
                new Emt('LABEL', '" class="col-sm-2 control-label"', input_param.text),
                blank_div,
            ]);
            group_div.blank_div = blank_div;
            if (input_param.handel_key !== undefined) {
                handle[input_param.handel_key] = blank_div;
            }
            if (input_param.isAutoAppend === undefined || input_param.isAutoAppend === true) {
                root_ele.addNodes([
                    group_div
                ]);
            }
            group_div.addNodes = blank_div.addNodes;
            return group_div;
        };

        handle.createInputDetail = function (obj) {
            return new Emt('DIV').setAttrsByStr('class="form-group"', '').addNodes([
                new Emt('LABEL', 'class="col-sm-2 control-label"', obj.label_text),
                new Emt('DIV', 'class="col-sm-10"').addNodes([
                    new Emt('em', '', obj.remark)
                ])
            ])
        };

        handle.appendInputDetail = function (obj) {
            root_ele.addNodes([
                handle.createInputDetail(obj)
            ]);
        };

        handle.markOutEleKey = function (handle_ele_key, ele) {
            handle[handle_ele_key] = ele;
            ele.is_changed = false;
            ele.setNewVal = function (val) {
                ele.value = val;
                ele.old_val = val;//注意， 如果为null的情况  ele.value!==ele.old_val
                ele.is_changed = false;
            }
            ele.setChangedVal = function (val) {
                ele.value = val;
                ele.is_changed = true;
            };
            ele.addEventListener('change', function () {
                ele.is_changed = true;
            })
            //注意， 如果为null的情况  ele.value!==ele.old_val
            ele.isValChanged = function () {
                return ele.is_changed;
            }
            //注意,这个不准， 如果为null的情况  ele.value!==ele.old_val   isValChanged && 1isOldVal() 两个组合确定两个都不一样了，才是可以修改的,以最大程度让  改了又改回去的操作  不提交，但是对于null,数组的情况，还是不够用
            ele.isOldVal = function () {
                return ele.old_val === ele.value;
            }
            ele.getValue = function () {
                return ele.value;
            }
            return handle[handle_ele_key]
        }

        handle.createInputText = function (label, placeholder, handle_ele_key, name_key) {
            handle.markOutEleKey(handle_ele_key, new Emt('INPUT').setAttrsByStr('type="text" class="form-control" placeholder="' + placeholder + '"', '').setPros({id: getEleRandId('input_text')}));
            if (name_key) {
                handle[handle_ele_key].name = input_opt.name_tpl.replace('$name_key', name_key);
            }
            root_ele.addNodes([
                handle.createInputGroup(label, handle[handle_ele_key])
            ]);
            return handle;
        }

        handle.createInputHide = function (handle_ele_key, name_key) {
            handle[handle_ele_key] = new Emt('INPUT').setAttrsByStr('type="hidden"', '').setPros({id: getEleRandId('input_hide')});
            if (name_key) {
                handle[handle_ele_key].name = input_opt.name_tpl.replace('$name_key', name_key);
            }
            root_ele.addNodes([handle[handle_ele_key]]);
            return handle;
        }

        handle.createInputTextArea = function (label, placeholder, handle_ele_key, name_key) {
            handle.markOutEleKey(
                handle_ele_key,
                new Emt('textarea').setAttrsByStr('rows="6" class="form-control" placeholder="' + placeholder + '"', '').setPros({id: getEleRandId('input_textarea')})
            );
            if (name_key) {
                handle[handle_ele_key].name = input_opt.name_tpl.replace('$name_key', name_key);
            }
            root_ele.addNodes([
                handle.createInputGroup(label, handle[handle_ele_key])
            ]);
            return handle;
        };

        handle.createSelect = function (label, val_maps, handle_ele_key, name_key) {
            handle.markOutEleKey(handle_ele_key, new Emt('select').setAttrsByStr('type="text" class="form-control" ', '').setPros({id: getEleRandId('input_select')}));
            if (name_key) {
                handle[handle_ele_key].name = input_opt.name_tpl.replace('$name_key', name_key);
            }
            root_ele.addNodes([
                handle.createInputGroup(label, handle[handle_ele_key])
            ]);
            val_maps.forEach(function (val_map) {
                handle[handle_ele_key].add(new Option(val_map.label, val_map.value));
            });
            return handle;
        }

        /**
         *
         * @param label
         * @param text
         * @param handle_ele_key
         * @param callback  接受俩参数  1:btn本身 2:form
         * @returns {{root_ele}}
         */
        handle.createSubmitButton = function (label, text, handle_ele_key, callback) {
            handle[handle_ele_key] = new Emt('BUTTON').setAttrsByStr('type="button" class="btn btn-default"  ', text).setPros({id: getEleRandId('btn_submit')});
            root_ele.addNodes([
                handle.createInputGroup(label, handle[handle_ele_key])
            ]);
            if (typeof callback === 'function') {
                handle[handle_ele_key].addEventListener('click', function () {
                    callback(this, root_ele);
                });
            }
            return handle;
        }


        handle.createInputFile = function (label, accept_file_types, handle_ele_key, name_key) {
            handle[handle_ele_key] = new Emt('input').setAttrsByStr('type="file"  accept="' + accept_file_types.join(',') + '"').setPros({id: getEleRandId('btn_submit')});
            if (name_key) {
                handle[handle_ele_key].name = input_opt.name_tpl.replace('$name_key', name_key);
            }
            root_ele.addNodes([
                new Emt('div').setPros({className: 'form-group'}).addNodes([
                    new Emt('label').setAttrsByStr('class="sr-only" for="' + handle[handle_ele_key].id + '"', label),
                    handle[handle_ele_key]
                ])
            ]);
            return handle;
        }

        handle.createProcess = function (label, handle_ele_key) {
            let process_uploading = new Emt('div').setAttrsByStr(' class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%;"');
            root_ele.addNodes([
                new Emt('div').setPros({className: 'form-group'}).addNodes([
                    new Emt('div').setPros({className: 'progress'}).addNodes([
                        process_uploading
                    ])
                ]),
            ]);
            handle[handle_ele_key] = process_uploading;

            handle[handle_ele_key].show = function () {
                process_uploading.parentElement.parentElement.classList.remove('hide');
                return process_uploading;
            }
            handle[handle_ele_key].hide = function () {
                process_uploading.parentElement.parentElement.classList.add('hide');
                return process_uploading;
            }
            handle[handle_ele_key].reset = function () {
                process_uploading.style.width = '0%';
                return process_uploading;
            }
            handle[handle_ele_key].process = function (int_num) {
                process_uploading.style.width = int_num.toString() + '%';
                return process_uploading;
            }
            return handle;
        }


        let opts = {
            color: {items: ['primary', 'success', 'info', 'warning', 'danger', 'link', 'default']},
            size: {items: ['lg', 'sm', 'xs', 'block']}
        };
        //设置不同颜色
        opts.color.set = function (color_key) {
            if (color_key !== '') {
                root_ele.classList.add('btn-' + color_key);
            }
            root_ele.classList.add('btn-' + color_key);
            opts.color.items.forEach(function (color) {
                if (color_key !== color) {
                    root_ele.classList.remove('btn-' + color);
                }
            });
            return handle;
        };
        //设置不同大小
        opts.size.set = function (size_key) {
            if (size_key !== '') {
                root_ele.classList.add('btn-' + size_key);
            }
            root_ele.classList.add('btn-' + color_key);
            opts.size.items.forEach(function (size) {
                if (size_key !== size) {
                    root_ele.classList.remove('btn-' + color);
                }
            })
            return handle;
        };
        handle.getRootEle = function () {
            return root_ele;
        }
        //handle.createInputText = createInputText;
        //handle.createSubmitButton = createSubmitButton;
        handle.opts = opts;
        root_ele.handle = handle;
        return handle;
    }


    let hammerYii2Bootstarp_Img = function (input_option) {
        let input_opt = input_option || {src: ''};
        let root_ele = new Emt('img').setPros({src: input_opt.src || '', id: getEleRandId('img')});
        let handle = {
            root_ele: root_ele,
        };
        handle.toggleClassName = function (class_name, type) {
            if (type) {
                if (type === 'add') {
                    root_ele.classList.add(class_name);
                } else {
                    root_ele.classList.remove(class_name);
                }
            } else {
                root_ele.classList.toggle(class_name);
            }
            return handle;
        }
        handle.toggleRounded = function (type) {
            return handle.toggleClassName('img-rounded', type);
        }
        handle.toggleCircle = function (type) {
            return handle.toggleClassName('img-circle', type);
        }
        handle.toggleThumbnail = function (type) {
            return handle.toggleClassName('img-thumbnail', type);
        }
        handle.toggleResponsive = function (type) {
            return handle.toggleClassName('img-responsive', type);
        }
        handle.getRootEle = function () {
            return root_ele;
        }

        root_ele.handle = handle;
        return handle;
    }


    let hammerYii2Bootstarp_Select = function (opt) {
        let root_ele = new Emt('select').setAttrsByStr('', '').setPros();
        let handle_this = {root_ele: root_ele, is_changed: false, old_val: false};
        handle_this.setMultiple = function (is_yes) {
            if (!is_yes) {
                root_ele.removeAttribute('multiple');
            } else {
                root_ele.setAttribute('multiple', 'multiple');
            }
            return handle_this;
        };

        handle_this.addItem = function (label, val, is_default) {
            let item = new Option(label, val);
            item.is_default = is_default || false;
            root_ele.add(item);
            return handle_this;
        };
        handle_this.addItem('不选择', '#####');
        handle_this.clearItems = function () {
            let len = root_ele.options.length
            if (root_ele.options[0].is_default === true) {
                for (let i = 1; i < len; i++) {
                    root_ele.options[1].remove(i);
                }
            } else {
                for (let i = 0; i < len; i++) {
                    root_ele.options[1].remove(i);
                }
            }
            return handle_this;
        };
        //可以是[string]，也可以是[{label:xx,val:xx}]
        handle_this.reloadDatas = function (list) {
            if (typeof list.forEach === 'function') {
                list.forEach(function (item) {
                    if (typeof item === 'string') {
                        handle_this.addItem(item, item);
                    } else {
                        handle_this.addItem(item.label, item.val);
                    }
                });
            } else {
                for (let tmp_key in list) {
                    handle_this.addItem(list[tmp_key], tmp_key);
                }
            }
            return handle_this;
        };
        if (opt && opt.list) {
            handle_this.reloadDatas(opt.list);
        }

        handle_this.getValue = function () {
            if (root_ele.multiple) {
                let vals = [];
                for (let i = 0; i < root_ele.options.length; i++) {
                    if (!root_ele.options[i].is_default && root_ele.options[i].selected) {
                        vals.push(root_ele.options[i].value);
                    }
                }
                return vals;
            } else {
                return root_ele.value;
            }
        };

        handle_this.loadVal = function (val) {
            if (root_ele.multiple) {
                let vals = (typeof val.forEach === 'function') ? val.map(function (v) {
                    return v.toString();
                }) : [val.toString()];
                for (let i = 0; i < root_ele.options.length; i++) {
                    if (vals.indexOf(root_ele.options[i].value) === -1) {
                        root_ele.options[i].selected = false;
                    } else {
                        root_ele.options[i].selected = true;
                    }
                }
            } else {
                root_ele.value = val;
            }
            return handle_this;
        };
        handle_this.setNewVal = function (val) {
            handle_this.old_val = val;
            handle_this.is_changed = false;
            handle_this.loadVal(val);
            return handle_this;
        }
        handle_this.isOldVal = function () {
            console.log('select change', handle_this.old_val, handle_this.getValue(), handle_this.old_val === handle_this.getValue());
            return handle_this.old_val === handle_this.getValue();
        }
        handle_this.isValChanged = function () {
            return handle_this.is_changed;
        }
        //这个是给 datagrid filter elements 用的=
        handle_this.getFilterValue = function () {
            return handle_this.getValue();
        }
        root_ele.addEventListener('change', function () {
            console.log('select change');
            handle_this.is_changed = true;
        })


        for (var i in handle_this) {
            if (typeof handle_this[i] === 'function') {
                handle_this.root_ele[i] = handle_this[i];
            }
        }

        return handle_this;
    };

    let hammerYii2Bootstarp_YesOrNo = function (opt) {
        let option_name = getEleRandId('hammer_input_option');

        let yes_ele = new Emt('INPUT', 'type="radio" value="1" class="hammer_input_option" ', '', {name: option_name});
        let no_ele = new Emt('INPUT', 'type="radio" value="2" class="hammer_input_option"', '', {name: option_name});
        let hide_ele = new Emt('input', 'type="hidden"');
        let root_ele = new Emt('DIV').addNodes([
            hide_ele,
            new Emt('LABEL').addNodes([
                yes_ele, new Emt('span', '', '是')
            ]),
            new Emt('LABEL').addNodes([
                no_ele, new Emt('span', '', '否')
            ])
        ]);
        let handle_this = {root_ele: root_ele, yes_ele: yes_ele, no_ele: no_ele, hide_ele: hide_ele};
        root_ele.is_changed = false;

        let updateSelectedVal = function () {
            if (yes_ele.checked === true) {
                hide_ele.value = yes_ele.value;
            } else if (no_ele.checked === true) {
                hide_ele.value = no_ele.value;
            } else {
                return false;
            }
        }

        yes_ele.addEventListener('change', function () {
            root_ele.is_changed = true;
            updateSelectedVal();
        })
        no_ele.addEventListener('change', function () {
            root_ele.is_changed = true;
            updateSelectedVal();
        })


        //注意， 如果为null的情况  ele.value!==ele.old_val
        handle_this.isValChanged = function () {
            return root_ele.is_changed;
        }
        //注意,这个不准， 如果为null的情况  ele.value!==ele.old_val
        handle_this.isOldVal = function () {
            return root_ele.old_val === hide_ele.value;
        }
        handle_this.getValue = function () {
            return hide_ele.value;
        }

        //可以是[string]，也可以是[{label:xx,val:xx}]
        handle_this.setNewVal = function (sta_val) {
            let tmp_val = parseInt(sta_val);
            if (tmp_val === 1) {
                yes_ele.click();
            } else {
                no_ele.click();
            }

            //root_ele.value = sta_val;
            root_ele.old_val = sta_val;//注意， 如果为null的情况  ele.value!==ele.old_val
            root_ele.is_changed = false;

            return handle_this;
        };
        if (opt && opt.val) {
            handle_this.setNewVal(opt.val);
        }
        handle_this.root_ele.getValue = handle_this.getValue;
        handle_this.root_ele.setNewVal = handle_this.setNewVal;
        handle_this.root_ele.isValChanged = handle_this.isValChanged;
        handle_this.root_ele.isOldVal = handle_this.isOldVal;


        handle_this.getValue = function () {
            return hide_ele.value;
        };
        return handle_this;
    };

    let addStyle = function () {
        if (!kl.id('hammer-bootstarp-style')) {
            document.body.append(
                new Emt('style').setAttrsByStr(
                    'id="hammer-bootstarp-style"',
                    ('    .hide_btn_sort_asc > .btn_sort_asc {\n' +
                        '        display: none;\n' +
                        '    }\n' +
                        '\n' +
                        '    .hide_btn_sort_desc > .btn_sort_desc {\n' +
                        '        display: none;\n' +
                        '    }\n' +
                        '\n' +
                        '    .hide_btn_sort > .btn_sort_asc, .hide_btn_sort > .btn_sort_desc {\n' +
                        '        display: none;\n' +
                        '    }\n' +
                        '\n' +
                        '    .hammer_input_option {\n' +
                        '        display: none;\n' +
                        '    }' +
                        '    input[class="hammer_input_option"] + span {\n' +
                        '        background: #FFF;\n' +
                        '        color: #000;\n' +
                        '    }\n' +
                        '\n' +
                        '    input[class="hammer_input_option"]:checked + span {\n' +
                        '        background: #000;\n' +
                        '        color: #FFF;\n' +
                        '    }'
                    )
                )
            )

        }
    }
    addStyle();
    return {
        modal: function (input) {
            return hammerYii2Bootstarp_Modal(input);
        },
        panel: function (input) {
            return hammerYii2Bootstarp_Panel(input);
        },
        button: function (input) {
            return hammerYii2Bootstarp_Button(input);
        },
        form: function (input) {
            return hammerYii2Bootstarp_Form(input);
        },
        img: function (input) {
            return hammerYii2Bootstarp_Img(input);
        },
        select: function (input) {
            return hammerYii2Bootstarp_Select(input);
        },
        yesOrNo: function (input) {
            return hammerYii2Bootstarp_YesOrNo(input);
        },

    }
}
let hammerBootstarpCreator = function () {
        let creater = {};

        creater.getEleRandId = function (prefix) {
            return prefix + '_' + (parseInt(Math.random(1, 2) * 10000000000).toString());
        };

        let bootstrapModalElement = function (init_config) {
            let self = this;
            if (init_config.easyClose !== true) {
                self.setAttribute("data-backdrop", "static");
            }
            self.libData = {};

            //命名为 show 某些情况下 会和jquery 冲突
            self.showModal = function () {
                $('#' + self.id).modal('show');
            };
            //命名为 hide 某些情况下 会和jquery 冲突
            self.hideModal = function () {
                $('#' + self.id).modal('hide');
            };


            let modal_title = new Emt('h4', 'class="modal-title"', init_config.title || '标题');
            let modal_body = new Emt('div', 'class="col-lg-12"');
            let modal_footer = new Emt('div', 'class="modal-foote"');

            self.addNodes([
                new Emt('div', 'class="modal-dialog"').addNodes([
                    new Emt('div', 'class="modal-content"').addNodes([
                        //头部
                        new Emt('div', 'class="modal-header"').addNodes([
                            new Emt('button', 'type="button" class="close" data-dismiss="modal" aria-hidden="true"', 'x'),
                            modal_title
                        ]),
                        //主体
                        new Emt('div', 'class="modal-body"').addNodes([
                            new Emt('div', 'class="row"').addNodes([
                                modal_body
                            ])
                        ]),
                        modal_footer
                    ])
                ])
            ]);
            console.log(self, init_config.title, modal_title);
            document.body.append(new Emt('div').addNodes([self]));


            self.libData.title = modal_title;
            self.libData.body = modal_body;
            self.libData.foot = modal_footer;
            self.setZindex = function (zindex_num) {
                self.style.zIndex = zindex_num;
                return self;
            };
            self.setTitleText = function (str) {
                modal_title.textContent = str;
                return self;
            };
            self.addBodyChildElements = function (eles) {
                modal_body.addNodes(eles);
                return self;
            };
            self.addFooterChildElements = function (eles) {
                modal_footer.addNodes(eles);
                return self;
            };
            return self;

        };
        /**
         *
         * @link https://www.runoob.com/bootstrap/bootstrap-modal-plugin.html  Bootstrap 模态框（Modal）插件
         * @param input_option
         * @returns HTMLElement|bootstrapModalElement
         */
        creater.createModal = function (input_option) {
            let init_config = input_option || {};
            let modalDiv = new Emt('div').setAttrsByStr('class="fade modal" role="dialog" :no-enforce-focus="true"').setPros({id: creater.getEleRandId('modal')});
            bootstrapModalElement.call(modalDiv, init_config);
            return modalDiv;
        };
        /**
         * @link https://www.runoob.com/bootstrap/bootstrap-panels.html Bootstrap 面板（Panels）

         * @returns {{title_ele, body_ele, root}}
         */
        creater.createPanel = function (input_param) {
            let init_config = input_param || {
                title: '面板标题',
                detail: '这是一个基本的面板',
                footer: '面板脚注'
            };
            let title = new Emt('H3').setAttrsByStr('class="panel-title"', init_config.title);
            let body = new Emt('DIV').setAttrsByStr('class="panel-body"', init_config.detail);
            let footer = new Emt('DIV').setAttrsByStr('class="panel-footer"', init_config.footer);
            let panelDiv = new Emt('DIV').setAttrsByStr('class="panel panel-primary"', '').addNodes([
                new Emt('DIV').setAttrsByStr('class="panel-heading"', '').addNodes([
                    title
                ]),
                body,
                footer
            ]);

            panelDiv.libData = {};

            panelDiv.libData.title = title;
            panelDiv.libData.body = body;
            panelDiv.libData.footer = footer;

            creater.initSetColor(panelDiv);
            return panelDiv;
        };

        creater.createTable = (input_param) => {
            let table_ele = new Emt('table', '' +
                'class="table table-bordered table-striped table-hover" ' +
                'style="' +
                'word-break:break-all;' +
                'word-wrap:break-word;' +
                'border-collapse: collapse;' +
                'table-layout: fixed;' +
                'width:100%;' +
                '"'
            );
            table_ele.cellKeys = [];
            table_ele.headerInfoMap = {};
            table_ele.headerInfos = [];
            table_ele.setHeaderInfos = (headerInfos) => {
                table_ele.headerInfos = headerInfos;
                table_ele.headerInfos.forEach((headerInfo) => {
                    table_ele.cellKeys.push(headerInfo.key);
                    table_ele.headerInfoMap[headerInfo.key] = headerInfo.text;
                });
            };
            if (input_param.headerInfos) {
                table_ele.setHeaderInfos(input_param.headerInfos);
            }

            table_ele.createRow = (input_config) => {
                let inputParam = input_config || {};
                let isHeaderRow = inputParam.isHeaderRow || false;
                let isColspan = inputParam.isColspan || false;
                let trow = table_ele.insertRow();

                trow.initCells = () => {
                    table_ele.cellKeys.forEach((cellKey) => {
                        trow[cellKey] = trow.insertCell();

                    });
                    return trow;
                };
                trow.loadContentMap = (contentMap) => {
                    table_ele.cellKeys.forEach((cellKey) => {
                        let contentType = typeof contentMap[cellKey];
                        if (['string', 'number'].indexOf(contentType) > -1) {
                            trow[cellKey].textContent = contentMap[cellKey];
                        } else if (contentType === 'object') {
                            if (typeof contentMap[cellKey].tagName === 'string') {
                                trow[cellKey].ele = contentMap[cellKey];
                                trow[cellKey].addNode(contentMap[cellKey]);
                            } else {
                                trow[cellKey].textContent = JSON.stringify(contentMap[cellKey]);
                            }
                        } else {
                            trow[cellKey].textContent = 'err';
                        }
                        //trow[headerInfo.key] = trow.insertCell();
                    });
                    return trow;
                };
                if (isColspan === true) {
                    trow.td = trow.insertCell();
                    trow.td.colSpan = table_ele.cellKeys.length;


                } else {
                    trow.initCells();
                }
                if (isHeaderRow) {
                    trow.loadContentMap(table_ele.headerInfoMap);
                }
                return trow;
            };
            return table_ele;
        };

        creater.createButton = function (input_param) {
            let init_config = input_param || {};
            let btn_ele = new Emt(init_config.tagName || 'button').setAttrsByStr('class="btn btn-default" ', input_param.contentText || 'contentText');
            btn_ele.libData = {};
            btn_ele.setAttribute('type', init_config.type || 'button');
            btn_ele.textContent = init_config.contentText || 'contentText';
            btn_ele.setPros({id: creater.getEleRandId('btn')});

            if (typeof init_config.clickCall === "function") {
                btn_ele.addEventListener('click', function () {
                    init_config.clickCall(btn_ele);
                });
            }
            creater.__initInputEle(btn_ele, input_param);
            creater.initSetSize(btn_ele);
            creater.initSetColor(btn_ele);
            return btn_ele;
        };

        let formInputGroupDivElement = function (init_config) {
            let formInputGroupDiv1 = this;
            formInputGroupDiv1.libData = {};
            // init_config.nameTpl = init_config.name_tpl || '$name_key';
            init_config.labelText = init_config.labelText === undefined ? 'labelText' : init_config.labelText;
            init_config.rowStyle = init_config.rowStyle === undefined ? 'horizontal' : init_config.rowStyle;

            let lableColClass = '';
            let inputColDivClass = '';
            if (init_config.rowStyle === 'horizontal') {

                let labelXsWidth = init_config.labelXsWidth || 2;
                let labelSmWidth = init_config.labelSmWidth || 2;
                let labelMdWidth = init_config.labelMdWidth || 2;
                let labelLgWidth = init_config.labelLgWidth || 2;

                let divXsWidth = (12 - labelXsWidth).toString();
                let divSmWidth = (12 - labelSmWidth).toString();
                let divMdWidth = (12 - labelMdWidth).toString();
                let divLgWidth = (12 - labelLgWidth).toString();

                lableColClass = `col-sm-${labelXsWidth} col-sm-${labelSmWidth} col-md-${labelMdWidth} col-lg-${labelLgWidth}`;
                inputColDivClass = `col-sm-${divXsWidth} col-sm-${divSmWidth} col-md-${divMdWidth} col-lg-${divLgWidth}`
            } else if (init_config.rowStyle === 'no_class') {

            } else if (init_config.rowStyle === 'inline') {

            }
            formInputGroupDiv1.libData.inputLabel = new Emt('LABEL', `class="${lableColClass} control-label"`, init_config.labelText);
            formInputGroupDiv1.libData.inputDiv = new Emt('DIV', `class="${inputColDivClass}"`);
            formInputGroupDiv1.libData.helpBlock = new Emt('span', 'class="help-block"');

            formInputGroupDiv1.addInputEle = function (inputEle) {
                formInputGroupDiv1.libData.inputDiv.addNode(inputEle);
                if (init_config.helpBlock !== false) {
                    formInputGroupDiv1.libData.inputDiv.addNode(formInputGroupDiv1.libData.helpBlock);
                }
                //console.log('xxxx', inputEle);
                if (inputEle.id) {
                    formInputGroupDiv1.libData.inputLabel.setAttribute('for', inputEle.id);
                }
                return formInputGroupDiv1;
            };
            formInputGroupDiv1.setText = (text) => {
                //console.log(`[${text}],[${init_config.labelText}]`);
                formInputGroupDiv1.libData.inputLabel.textContent = text;
                return formInputGroupDiv1;
            };
            formInputGroupDiv1.setDetail = (detail) => {
                formInputGroupDiv1.libData.helpBlock.textContent = detail;
                return formInputGroupDiv1;
            };
            formInputGroupDiv1.addNodes([
                formInputGroupDiv1.libData.inputLabel,
                formInputGroupDiv1.libData.inputDiv,
            ]);
            return formInputGroupDiv1;
        }

        /**
         *
         * @param input_param
         * @returns {HTMLElement|formInputGroupDivElement}
         */
        creater.createFormInputGroupDiv = function (input_param) {
            let init_config = input_param || {};
            let group_div = new Emt('DIV').setAttrsByStr('class="form-group"', '');
            formInputGroupDivElement.call(group_div, init_config);
            return group_div;
        };


        creater.initSetColor = (inputEle) => {
            inputEle.libData.color = {items: ['primary', 'success', 'info', 'warning', 'danger', 'link', 'default']};
            /**
             * 按钮设置不同颜色
             * @param color_key
             * @returns {HTMLElement}
             */
            inputEle.setColor = function (color_key) {
                if (color_key !== '') {
                    inputEle.classList.add('btn-' + color_key);
                }
                inputEle.classList.add('btn-' + color_key);
                inputEle.libData.color.items.forEach(function (color) {
                    if (color_key !== color) {
                        inputEle.classList.remove('btn-' + color);
                    }
                });
                return inputEle;
            };
        };
        creater.initSetSize = (inputEle) => {
            inputEle.libData.size = {items: ['lg', 'sm', 'xs', 'block']};
            //设置不同大小
            inputEle.setSize = function (size_key) {
                if (size_key !== '') {
                    inputEle.classList.add('btn-' + size_key);
                }
                inputEle.classList.add('btn-' + size_key);
                inputEle.libData.size.items.forEach(function (size) {
                    if (size_key !== size) {
                        inputEle.classList.remove('btn-' + size_key);
                    }
                });
                return inputEle;
            };
        };

        creater.__initInputEle = function (inputEle, input_param) {
            if (input_param.placeholder !== undefined) {
                inputEle.placeholder = input_param.placeholder;
            }
            inputEle.libData = inputEle.libData || {};
            inputEle.libData.val = inputEle.libData.val || {
                init: undefined
            };

            inputEle.getVal = inputEle.getVal || function () {
                return inputEle.value;
            };
            inputEle.setVal = inputEle.setVal || function (val) {
                inputEle.value = val;
            };


            inputEle.setInitVal = inputEle.setInitVal || function (val) {
                inputEle.setVal(val);
                inputEle.libData.val.init = inputEle.getVal();//因为会出现设置失败的情况，比如select 指定了一个不存在的 option val,
                return inputEle;
            };
            inputEle.getInitVal = inputEle.getInitVal || function (val) {
                return inputEle.val.init;
            };

            inputEle.rollBackInitVal = inputEle.rollBackInitVal || function () {
                inputEle.setInitVal(inputEle.val.init);
                return inputEle;
            };


            inputEle.setChangedVal = inputEle.setChangedVal || function (val) {
                inputEle.__setVal(val);
                return inputEle;
            };
            inputEle.acceptValChanged = inputEle.acceptValChanged || function () {
                inputEle.val.init = inputEle.getVal();
                return inputEle;
            };

            inputEle.isChange = inputEle.isChange || function () {
                return !(inputEle.getInitVal() === inputEle.getVal());
            };


            inputEle.setClickCall = inputEle.setClickCall || function (fun) {
                inputEle.addEventListener('click', function () {
                    fun(this);
                });
            };

            inputEle.setOnChange = inputEle.setOnChange || ((fun) => {
                //如果是复杂输入，有多个 input 触发，应该全都加上
                inputEle.addEventListener('change', () => {
                    fun(inputEle);
                });
            });
            inputEle.setItems = inputEle.setItems || ((items) => {
                if (typeof inputEle.libData.setItems === 'function') {
                    inputEle.setItems(items);
                }
            });


        };

        let __baseInput = function (input_param) {
            let baseInput1 = this;

            if (input_param.placeholder !== undefined) {
                baseInput1.placeholder = input_param.placeholder;
            }

            baseInput1.getVal = baseInput1.trueInputElement ? baseInput1.trueInputElement.getVal : function () {
                return baseInput1.value;
            };
            baseInput1.setVal = baseInput1.trueInputElement ? baseInput1.trueInputElement.setVal : function (val) {
                baseInput1.lastTrySetVal = val;
                baseInput1.value = val;
                baseInput1.lastSetVal = baseInput1.value;
                //  console.log('setVal', [val, baseInput1.lastTrySetVal, baseInput1.value, baseInput1.lastSetVal], baseInput1);//除了 lastTrySetVal 都会自动变化
            };

            baseInput1.resetByDefaultVal = baseInput1.trueInputElement ? baseInput1.trueInputElement.resetByDefaultVal : function () {
                baseInput1.lastTrySetVal = undefined;
                baseInput1.value = input_param.defaultVal || '';
                baseInput1.lastSetVal = undefined;
                //  console.log('setVal', [val, baseInput1.lastTrySetVal, baseInput1.value, baseInput1.lastSetVal], baseInput1);//除了 lastTrySetVal 都会自动变化
            };


            baseInput1.isChange = baseInput1.trueInputElement ? baseInput1.trueInputElement.isChange : function () {
                return !(baseInput1.lastSetVal === baseInput1.getVal());
            };
            return baseInput1;
        };

        let __intNumberInput = function (input_param) {
            let intNumberInput1 = this;
            __baseInput.call(intNumberInput1, input_param);
            intNumberInput1.getVal = function () {
                return parseInt(intNumberInput1.value);
            };
            intNumberInput1.setVal = function (val) {
                let newNum = parseInt(val);
                if (isNaN(newNum)) {
                    console.error(val);
                    throw 'intNumberInput 不能接受 NaN';
                }
                intNumberInput1.value = newNum;
                intNumberInput1.lastSetVal = newNum;
            };
            return intNumberInput1;
        };

        let __datalist = function () {
            let datalist1 = this;
            datalist1.libData = {options: []};
            ;
            datalist1.addItem = function (text, val) {
                let new_opt = new Option(text, val);
                datalist1.libData.options.push(new_opt);
                datalist1.addNode(new_opt);
            };
            datalist1.setItems = function (items) {
                datalist1.libData.options.forEach((old_opt) => {
                    old_opt.remove();
                });
                items.forEach(function (item) {
                    let new_opt = new Option(item.text, item.val);
                    datalist1.libData.options.push(new_opt);
                    datalist1.addNode(new_opt);
                });
            };
            //document.body.append(datalist1);
            return datalist1;
        };
        let __withDroplistTextInput = function (input_param) {
            let textInput1 = this;
            __baseInput.call(textInput1, input_param);
            textInput1.datalisEle = creater.createDatalist();
            // textInput1.list = textInput1.datalisEle.id;

            textInput1.setItems = function (items) {
                textInput1.datalisEle.setItems(items);
                return textInput1;
            };

            textInput1.initDroplist = () => {
                textInput1.setAttrs({list: textInput1.datalisEle.id});
                document.body.append(textInput1.datalisEle);
                return textInput1;
            };
            if (input_param.items && input_param.items.length > 0) {
                textInput1.setItems(input_param.items);
                textInput1.initDroplist();
            }
            return textInput1;
        }

        let __checkboxInput = function (input_param) {
            let checkboxInput1 = this;
            __baseInput.call(checkboxInput1, input_param);
            checkboxInput1.stateMap = input_param.stateMap || {true: true, false: false};
            checkboxInput1.setStateMap = function (stateMap) {
                checkboxInput1.stateMap = stateMap;
                return checkboxInput1;
            };

            checkboxInput1.getVal = function () {
                return checkboxInput1.checked ? checkboxInput1.stateMap.true : checkboxInput1.stateMap.false;
            };
            checkboxInput1.setVal = function (val) {
                if (val === checkboxInput1.stateMap.true) {
                    checkboxInput1.lastSetVal = val;
                    checkboxInput1.checked = true;
                } else if (val === checkboxInput1.stateMap.false) {
                    checkboxInput1.lastSetVal = val;
                    checkboxInput1.checked = false;
                } else {
                    console.log(val, checkboxInput1.stateMap);
                    throw '__checkboxInput.setVal 设置的值，不在预设范围内';
                }
            };
            return checkboxInput1;
        }

        /**
         *
         * @param input_param
         * @returns {__selectInput|__baseInput}
         * @private
         */
        let __selectInput = function (input_param) {
            let selectInput1 = this;
            __baseInput.call(selectInput1, input_param);

            selectInput1.libData = {options: []};
            selectInput1.addItem = function (text, val) {
                let new_opt = new Option(text, val);
                selectInput1.libData.options.push(new_opt);
                selectInput1.add(new_opt);
            };
            selectInput1.setItems = function (items) {
                selectInput1.libData.options.forEach((old_opt) => {
                    old_opt.remove();
                });
                selectInput1.libData.items = items;
                selectInput1.libData.items.forEach(function (item) {
                    let new_opt = new Option(item.text, item.val);
                    selectInput1.libData.options.push(new_opt);
                    selectInput1.add(new_opt);
                });
                if (selectInput1.lastTrySetVal) {
                    selectInput1.setVal(selectInput1.lastTrySetVal);
                    //  console.log(['重设1 ', selectInput1.value, selectInput1.lastSetVal, selectInput1.getVal()]);//这几个值会动态变化
                }
            };
            if (input_param && input_param.items && typeof input_param.items.forEach === "function") {
                selectInput1.setItems(input_param.items);
            }

            return selectInput1;
        }

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__baseInput}
         */
        creater.createTextInput = function (input_param) {
            let text_input_ele = new Emt('INPUT', 'type="text"').setPros({id: creater.getEleRandId('text_input')});
            __baseInput.call(text_input_ele, input_param);
            return text_input_ele;
        };


        /**
         * 带有下拉选项 text input
         * @param input_param
         * @returns {HTMLElement|__withDroplistTextInput|__baseInput}
         */
        creater.createWithDroplistTextInput = function (input_param) {
            let text_input_ele = new Emt('INPUT', 'type="text"').setPros({id: creater.getEleRandId('text_input')});
            __withDroplistTextInput.call(text_input_ele, input_param);
            return text_input_ele;
        };

        /**
         *
         * @returns {HTMLElement|__datalist}
         */
        creater.createDatalist = function () {
            let datalist_ele = new Emt('datalist').setPros({id: creater.getEleRandId('datalist')});
            __datalist.call(datalist_ele);
            return datalist_ele;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__intNumberInput|__baseInput}
         */
        creater.createNumberInput = function (input_param) {
            let text_input_ele = new Emt('INPUT', 'type="number"').setPros({id: creater.getEleRandId('number_input')});
            __intNumberInput.call(text_input_ele, input_param);
            return text_input_ele;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__checkboxInput|__baseInput}
         */
        creater.createCheckboxInput = function (input_param) {
            let checkbox_input_ele = new Emt('INPUT', 'type="checkbox"').setPros({id: creater.getEleRandId('single_checkbox')});
            __checkboxInput.call(checkbox_input_ele, input_param);
            return checkbox_input_ele;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__checkboxInput|__baseInput}
         */
        creater.createEmojiSpanCheckbox = function (input_param) {
            let checkbox = new Emt('INPUT', 'type="checkbox" class="hidden"');
            __checkboxInput.call(checkbox, input_param);
            let label = new Emt('label').setPros({id: creater.getEleRandId('single_checkbox')}).addNodes([
                checkbox,
                new Emt('span'),
            ]);
            label.trueInputElement = checkbox;

            label.setStateMap = (stateMap) => {
                checkbox.setStateMap(stateMap);
                return label;
            }
            __baseInput.call(label, input_param);
            return label;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__baseInput}
         */
        creater.createHideInput = function (input_param) {
            let hidden_input_ele = new Emt('INPUT', 'type="hidden"').setPros({id: creater.getEleRandId('hide_input')});
            __baseInput.call(hidden_input_ele, input_param);
            return hidden_input_ele;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__baseInput}
         */
        creater.createTextArea = function (input_param) {
            let textarea_ele = new Emt('textarea', 'rows="6"').setPros({id: creater.getEleRandId('textarea_input')});
            __baseInput.call(textarea_ele, input_param);
            return textarea_ele;
        };

        /**
         *
         * @param input_param
         * @returns {HTMLElement|__selectInput}
         */
        creater.createSelect = function (input_param) {
            let select_input_ele = new Emt('select').setPros({id: creater.getEleRandId('select_input')});
            __selectInput.call(select_input_ele, input_param);
            return select_input_ele;
        };


        creater.createMultipleSelect = function (input_param) {
            let select_input_ele = new Emt('select').setAttrs({multiple: 'multiple'}).setPros({id: creater.getEleRandId('select_input')});
            __selectInput.call(select_input_ele, input_param);

            select_input_ele.setAttrs({size: 1});

            select_input_ele.getVal = function () {
                let vals = [];
                for (let i = 0; i < select_input_ele.libData.options.length; i++) {
                    if (select_input_ele.options[i].selected) {
                        vals.push(select_input_ele.libData.options[i].value);
                    }
                }
                return vals;
            };

            select_input_ele.setVal = function (vals) {
                for (let i = 0; i < select_input_ele.libData.options.length; i++) {
                    if (vals.indexOf(select_input_ele.libData.options[i].value) === -1) {
                        select_input_ele.libData.options[i].selected = false;
                    } else {
                        select_input_ele.libData.options[i].selected = true;
                    }
                }
            };

            select_input_ele.addEventListener('focus', function () {
                select_input_ele.setAttrs({size: select_input_ele.libData.items.length});
            });
            select_input_ele.addEventListener('focusout', function () {
                select_input_ele.setAttrs({size: 1});
            });

            return select_input_ele;
        };


        creater.createCheckBoxs = function (input_param) {
            let checkboxs_div = new Emt('div', 'class="overflow-y-hidden"');
            checkboxs_div.setPros({id: creater.getEleRandId('checkboxs_div')});
            __baseInput.call(checkboxs_div, input_param);
            checkboxs_div.toggleBtn = new Emt('button', 'type="button"',).addNodes([
                //  new Emt('span','','📁'),
                //  new Emt('span','class="glyphicon glyphicon-circle-arrow-down"'),
                new Emt('span', 'class="glyphicon glyphicon-list"')
            ]);
            checkboxs_div.checkboxListDiv = new Emt('div');
            checkboxs_div.addNodes([
                checkboxs_div.toggleBtn,
                checkboxs_div.checkboxListDiv
            ]);
            checkboxs_div.libData = {checkboxs: [], items: []};

            checkboxs_div.setItems = function (items) {
                if (items === undefined || typeof items.forEach !== "function") {
                    console.log(items);
                    console.trace();
                    throw '错误的items 不是数组';
                }
                checkboxs_div.libData.checkboxs.forEach((checkbox) => {
                    checkbox.parentElement.remove();
                });

                checkboxs_div.libData.items = items;
                checkboxs_div.libData.checkboxs = [];
                checkboxs_div.libData.items.forEach(function (item) {
                    let check_div = new Emt('div');
                    let item_checkbox = new Emt('input', 'type="checkbox"', '', {dataVal: item.val, value: item.val});
                    checkboxs_div.checkboxListDiv.addNode(check_div.addNodes([
                        new Emt('label').addNodes([
                            item_checkbox,
                            new Emt('span', '', item.text)
                        ])
                    ]));
                    checkboxs_div.libData.checkboxs.push(item_checkbox);
                });
                if (checkboxs_div.lastTrySetVal !== undefined) {
                    checkboxs_div.setVal(checkboxs_div.lastTrySetVal);
                }
            };
            if (typeof input_param.items === "function") {
                checkboxs_div.setItems(input_param.items);
            }


            checkboxs_div.val = {
                init: undefined
            };
            // input_param.keepClass = true;
            input_param.lastValType = '';
            creater.__initInputEle(checkboxs_div, input_param);

            checkboxs_div.getVal = function () {
                let vals = [];
                checkboxs_div.libData.checkboxs.forEach(function (checkbox) {
                    if (checkbox.checked === true) {
                        vals.push(checkbox.dataVal);
                    }
                });
                let res;
                switch (input_param.lastValType) {
                    case 'string':
                        res = vals.join(',');
                        break;
                    case 'json':
                        res = JSON.stringify(vals);
                        break;
                    case 'array':
                    default:
                        res = vals;
                        break;
                }
                return res;
            };

            checkboxs_div.setVal = function (input_val) {
                let array_val = [];
                if (typeof input_val === "object") {
                    if (typeof input_val.forEach === "function") {
                        array_val = input_val;
                        input_param.lastValType = 'array';
                    } else {
                        console.error(val_type, input_val);
                        throw '参数不是数组';
                    }
                } else if (typeof input_val === 'string') {
                    if (input_val[0] === '[' && input_val[input_val.length - 1] === ']') {
                        try {
                            array_val = JSON.parse(input_val);
                            input_param.lastValType = 'json';
                        } catch (e) {
                            console.error(val_type, input_val);
                            throw '参数不是数组json 解析失败';
                        }
                    } else {
                        array_val = input_val.split(',');
                        input_param.lastValType = 'string';
                    }
                } else {
                    console.error(input_val);
                    throw '参数不是数组,也不是字符串';
                }

                checkboxs_div.lastTrySetVal = input_val;

                checkboxs_div.libData.checkboxs.forEach(function (checkbox) {
                    // console.log(checkbox.dataVal);
                    if (array_val.indexOf(checkbox.dataVal) === -1) {
                        checkbox.checked = false;
                    } else {
                        checkbox.checked = true;
                    }
                });

                return checkboxs_div;
            };

            checkboxs_div.toggleBtn.addEventListener('click', () => {
                checkboxs_div.classList.toggle('overflow-y-hidden');
                checkboxs_div.classList.toggle('height-auto');
            });

            return checkboxs_div;
        };

        creater.createInputTagsDiv = function (input_param) {
            input_param = input_param || {};
            let inputTags_div = new Emt('div', 'class="xxxx"');
            inputTags_div.setPros({id: creater.getEleRandId('input_tags_div')});
            inputTags_div.libData = {
                tags: [],
                onChange: () => {
                }
            };

            inputTags_div.libData.tagsDiv = new Emt('div');
            inputTags_div.libData.textInputEle = creater.createTextInput(input_param);
            inputTags_div.addNodes([
                inputTags_div.libData.tagsDiv.addNodes([]),
                new Emt('div', 'class=""').addNodes([
                    inputTags_div.libData.textInputEle,
                ])
            ]);


            inputTags_div.setOnchange = (fun) => {
                inputTags_div.onChange = fun;
                //fun(inputTags_div);
            };

            inputTags_div.clearTags = () => {
                let ar = Object.values(inputTags_div.libData.tagsDiv.childNodes);
                ar.forEach((btn, tmp_i) => {
                    btn.remove();
                    ar[tmp_i] = null;
                });
                inputTags_div.libData.tags = [];
                return inputTags_div;
            };

            inputTags_div.setTags = function (tags) {
                if (tags === undefined || typeof tags.forEach !== "function") {
                    console.log(tags);
                    console.trace();
                    throw '错误的items 不是数组';
                }
                tags = tags.map((item) => {
                    return item.trim();
                });
                inputTags_div.clearTags();
                inputTags_div.libData.tags = tags;

                inputTags_div.libData.tags.forEach((item_str) => {
                    let btn = new Emt('label', 'class="btn btn-lg" style=""', item_str, {val: item_str}).addNodes([
                        new Emt('span', 'class="close"', 'x')
                    ]);
                    inputTags_div.libData.tagsDiv.addNodes([
                        btn
                    ]);
                    btn.addEventListener('click', () => {
                        btn.remove();
                        delete btn;
                        inputTags_div.flushTags();
                        inputTags_div.dispatchEvent(new Event('change'));
                    });
                });

                return inputTags_div;
            };

            inputTags_div.__loadStringContent = (str) => {
                if (str === undefined) {
                    let strs = Array.from(new Set(
                        inputTags_div.libData.textInputEle.value.trim().split(/[\s,， 、\/]+/gi).filter((str) => {
                            return str.length > 0;
                        })
                    ));
                    inputTags_div.setTags(strs);
                } else {
                    inputTags_div.setTags(
                        Array.from(new Set(
                            str.trim().split(/[\s,， 、\/]+/gi).filter((str) => {
                                return str.length > 0;
                            })
                        ))
                    );
                }

            };


            inputTags_div.flushTags = () => {
                let ar = [];
                Object.values(inputTags_div.libData.tagsDiv.childNodes).forEach((btn) => {
                    ar.push(btn.val);
                });
                inputTags_div.libData.tags = ar;
                inputTags_div.libData.textInputEle.value = ar.join(',') + ' ';

                return inputTags_div;
            };


            inputTags_div.getVal = function () {
                inputTags_div.flushTags();
                return inputTags_div.libData.tags;
            };

            inputTags_div.setVal = function (array_val) {
                array_val = typeof array_val === "object" && typeof array_val.forEach === "function" ? array_val : [];
                inputTags_div.libData.textInputEle.value = array_val.join(',');
                inputTags_div.__loadStringContent();
                return inputTags_div;
            };

            inputTags_div.isChange = function () {
                return !(JSON.stringify(inputTags_div.getInitVal()) === JSON.stringify(inputTags_div.getVal()));
            };
            creater.__initInputEle(inputTags_div, input_param);

            inputTags_div.libData.textInputEle.addEventListener('focusout', function () {

            });
            //datalist 选中的，展现在标签上
            inputTags_div.libData.textInputEle.addEventListener('keyup', function (e) {
                // console.log(e.key, e.keyCode);
                if ([' ', ',', '，', '、', '/', 'Backspace'].indexOf(e.key) !== -1) {
                    inputTags_div.__loadStringContent();
                }
            });
            inputTags_div.libData.textInputEle.addEventListener('change', function () {
                inputTags_div.__loadStringContent();
                inputTags_div.dispatchEvent(new Event('change'));
            });

            return inputTags_div;
        };

        creater.createSearchTags = function (input_param) {
            let searchTags_div = new Emt('div', 'class="xxxx"');
            searchTags_div.setPros({id: creater.getEleRandId('search_tags_div')});
            searchTags_div.libData = {
                vals: [],
                checkboxs: [],
                items: [],
                onChange: () => {
                }
            };

            searchTags_div.libData.toggleSearchBtn = creater.createButton({type: 'button', contentText: '🔍'});
            searchTags_div.libData.tagsDiv = new Emt('div');
            searchTags_div.libData.droplistDiv = new Emt('div', 'class="hide"');
            searchTags_div.libData.textInputEle = creater.createTextInput(input_param);
            searchTags_div.libData.datalistEle = creater.createDatalist(input_param);


            searchTags_div.libData.textInputEle.list = searchTags_div.libData.datalistEle.id;
            searchTags_div.libData.textInputEle.setAttrs({list: searchTags_div.libData.datalistEle.id});
            searchTags_div.addNodes([
                searchTags_div.libData.tagsDiv.addNodes([
                    searchTags_div.libData.toggleSearchBtn
                ]),
                searchTags_div.libData.droplistDiv.addNodes([
                    searchTags_div.libData.textInputEle,
                    searchTags_div.libData.datalistEle
                ])
            ]);


            searchTags_div.setOnchange = (fun) => {
                searchTags_div.onChange = fun;
                //fun(searchTags_div);
            };
            searchTags_div.trySelectItemVal = function (inputVal) {
                inputVal = inputVal.toString();
                if (inputVal.length > 0 && searchTags_div.libData.vals.indexOf(inputVal) === -1) {
                    searchTags_div.libData.items.forEach((item) => {
                        if (item.val === inputVal) {
                            let item_checkbox = new Emt('input', 'type="checkbox"', '', {dataVal: item.val, value: item.val, checked: true});
                            if (input_param.nameVar) {
                                item_checkbox.setAttrs({name: `${input_param.nameVar}[]`});
                            }
                            searchTags_div.libData.tagsDiv.addNode(
                                new Emt('label').addNodes([
                                    item_checkbox,
                                    new Emt('span', '', item.text)
                                ])
                            );
                            searchTags_div.libData.checkboxs.push(item_checkbox);
                            searchTags_div.libData.vals.push(item.val);
                            item_checkbox.addEventListener('change', function () {
                                if (item_checkbox.checked === false) {
                                    item_checkbox.parentElement.remove();
                                }
                                searchTags_div.libData.vals = searchTags_div.getVal();
                                //   console.log('vals', searchTags_div.libData.vals);
                                searchTags_div.onChange(searchTags_div);
                            });
                            searchTags_div.libData.vals = searchTags_div.getVal();
                            //  console.log('vals', searchTags_div.libData.vals);

                        }
                    });
                } else {
                    console.log(' searchTags_div.trySelectItemVal skip:', inputVal.length, searchTags_div.libData.vals.indexOf(inputVal));
                }
            };


            searchTags_div.setItems = function (items) {
                if (items === undefined || typeof items.forEach !== "function") {
                    console.log(items);
                    console.trace();
                    throw '错误的items 不是数组';
                }
                items = items.map((item) => {
                    item.val = item.val.toString();
                    return item;
                });

                searchTags_div.libData.items = items;
                searchTags_div.libData.datalistEle.setItems(items);
                return searchTags_div;
            };
            if (input_param.items !== undefined && typeof input_param.items.forEach === "function") {
                searchTags_div.setItems(input_param.items);
            }


            searchTags_div.libData.val = {
                init: undefined
            };
            input_param.keepClass = true;

            searchTags_div.getVal = function () {
                let vals = [];
                searchTags_div.libData.checkboxs.forEach(function (checkbox) {
                    if (checkbox.checked === true) {
                        vals.push(checkbox.dataVal);
                    }
                });
                return vals;
            };

            searchTags_div.setVal = function (array_val) {
                array_val = typeof array_val === "object" && typeof array_val.forEach === "function" ? array_val : [];
                //console.log(array_val,searchTags_div.libData.checkboxs);
                searchTags_div.libData.checkboxs.forEach(function (checkbox) {
                    checkbox.remove();
                });
                searchTags_div.libData.checkboxs = [];
                searchTags_div.libData.vals = [];
                array_val.forEach((val) => {
                    searchTags_div.trySelectItemVal(val);
                });


                return searchTags_div;
            };

            searchTags_div.isChange = function () {
                return !(JSON.stringify(searchTags_div.getInitVal()) === JSON.stringify(searchTags_div.getVal()));
            };
            creater.__initInputEle(searchTags_div, input_param);


            searchTags_div.libData.setRemoteItems = searchTags_div.libData.__setRemoteItems;

            searchTags_div.libData.toggleSearchBtn.addEventListener('click', function () {
                searchTags_div.libData.droplistDiv.classList.toggle('hide');
            });
            searchTags_div.libData.textInputEle.addEventListener('focusout', function () {
                searchTags_div.libData.droplistDiv.classList.add('hide');
                searchTags_div.libData.textInputEle.value = '';
            });

            //datalist 选中的，展现在标签上
            searchTags_div.libData.textInputEle.addEventListener('change', function () {
                searchTags_div.trySelectItemVal(searchTags_div.libData.textInputEle.value);
            });


            return searchTags_div;
        };

        creater.createFileInput = function (input_param) {
            let file_input = new Emt('input', 'type="file" ').setPros({id: creater.getEleRandId('file_input')});
            file_input.libData = {ele: {root: file_input}};
            creater.__initInputEle(file_input, input_param);
            if (input_param && input_param.acceptFileTypes && typeof input_param.acceptFileTypes === 'function' && input_param.acceptFileTypes.length) {
                file_input.setAttribute('accept', input_param.acceptFileTypes.join(','));
            }
            return file_input;
        };


        creater.createProcessDiv = function (input_param) {
            let innerDiv = new Emt('div', ' class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%;"');
            let outerDiv = new Emt('div').setPros({className: 'progress'}).addNodes([
                innerDiv
            ]);
            outerDiv.setPros({id: creater.getEleRandId('processdiv')});
            outerDiv.libData = {
                processVal: input_param.val || 0,
            };
            outerDiv.setVal = function (int_num) {
                outerDiv.libData.processVal = int_num;
                innerDiv.style.width = int_num.toString() + '%';
                return outerDiv;
            };
            outerDiv.getVal = () => {
                return outerDiv.libData.processVal;
            };
            outerDiv.setVal(outerDiv.libData.processVal);
            outerDiv.setText = (text) => {
                innerDiv.textContent = text;
            };

            return outerDiv;
        };

        let preCreator = function (init_param) {
            let preCreater1 = this;
            preCreater1.param = init_param || {};
            preCreater1.onCreate = false;
            preCreater1.ele = false;
            preCreater1.setLabelText = function (labelText) {
                preCreater1.param.labelText = labelText;
                return preCreater1;
            };

            /**
             * @param detail
             * @returns {preCreator}
             */
            preCreater1.setHelpblockDetail = function (detail) {
                if (detail === false) {
                    preCreater1.param.detail = '';
                    preCreater1.param.helpBlock = false;
                } else {
                    preCreater1.param.detail = detail;
                }
                return preCreater1;
            };
            /**
             *
             * @param contentText
             * @returns {preCreator}
             */
            preCreater1.setContentText = function (contentText) {
                preCreater1.param.contentText = contentText;
                return preCreater1;
            };
            /**
             *
             * @returns {preCreator}
             */
            preCreater1.setKeepClass = () => {
                preCreater1.param.keepClass = true;
                return preCreater1;
            };

            /**
             *
             * @param placeHolder
             * @returns {preCreator}
             */
            preCreater1.setPlaceHolder = function (placeHolder) {
                preCreater1.param.placeHolder = placeHolder;
                return preCreater1;
            };
            /**
             *
             * @param nameVar
             * @returns {preCreator}
             */
            preCreater1.setNameVar = function (nameVar) {
                preCreater1.param.nameVar = nameVar;
                return preCreater1;
            };
            /**
             *
             * @param indexKey
             * @returns {preCreator}
             */
            preCreater1.setIndexKey = function (indexKey) {
                preCreater1.param.indexKey = indexKey;
                return preCreater1;
            };
            /**
             *
             * @param type
             * @returns {preCreator}
             */
            preCreater1.setType = function (type) {
                preCreater1.param.type = type;
                return preCreater1;
            };
            /**
             *
             * @param items
             * @returns {preCreator}
             */
            preCreater1.setItems = function (items) {
                preCreater1.param.items = items;
                return preCreater1;
            };
            /**
             * 追加参数
             * @param pros  { key:val ,k1:v1}
             * @returns {preCreator}
             */
            preCreater1.appendNotExistedParams = (pros) => {
                for (let k in pros) {
                    preCreater1.param[k] = pros[k];
                }
                return preCreater1;
            };
            /**
             * 实例化之后，必选先调用这个方法，供input element 创建之后，去调用onCreate
             * @param fun
             * @returns {preCreator}
             */
            preCreater1.setOnCreate = function (fun) {
                preCreater1.onCreate = fun;
                return preCreater1;
            };
            /**
             *
             * @param fun
             * @returns {preCreator}
             */
            preCreater1.setClickCall = function (fun) {
                preCreater1.param.clickCall = fun;
                return preCreater1;
            };
            /**         *
             * @param type
             * @returns {HTMLElement}
             */
            preCreater1.create = function (type) {
                let preCreater1_ele = false;
                if (type === 'text') {
                    preCreater1_ele = creater.createTextInput(preCreater1.param);

                } else if (type === 'number') {
                    preCreater1_ele = creater.createNumberInput(preCreater1.param);
                } else if (type === 'select') {
                    preCreater1_ele = creater.createSelect(preCreater1.param);
                } else if (type === 'checkbox') {
                    preCreater1_ele = creater.createCheckboxInput(preCreater1.param);
                } else if (type === 'button') {
                    preCreater1_ele = creater.createButton(preCreater1.param);
                } else if (type === 'textarea') {
                    preCreater1_ele = creater.createTextArea(preCreater1.param);
                } else if (type === 'hide') {
                    preCreater1_ele = creater.createHideInput(preCreater1.param);
                } else if (type === 'checkbox_list') {
                    preCreater1_ele = creater.createCheckBoxs(preCreater1.param);
                } else if (type === 'file') {
                    preCreater1_ele = creater.createFileInput(preCreater1.param);
                } else if (type === 'text1') {
                    preCreater1_ele = creater.createTextInput(preCreater1.param);
                } else if (type === 'search_tags') {
                    preCreater1_ele = creater.createSearchTags(preCreater1.param);
                } else if (type === 'process') {
                    preCreater1_ele = creater.createProcessDiv(preCreater1.param);
                } else if (type === 'div') {
                    preCreater1_ele = new Emt('div');
                    preCreater1_ele.setPros({id: creater.getEleRandId('div')});
                } else if (type === 'droplist_text') {
                    preCreater1_ele = creater.createWithDroplistTextInput(preCreater1.param);
                } else {
                    preCreater1_ele = creater.createTextInput(preCreater1.param);
                }
                if (preCreater1.onCreate !== false) {
                    preCreater1.onCreate(preCreater1_ele, preCreater1.param);
                }
                return preCreater1_ele;
            };


            preCreater1.__execOnCreate = function (preCreater1_ele) {
                if (preCreater1.onCreate !== false) {
                    preCreater1.onCreate(preCreater1_ele, preCreater1.param);
                }
            }
            //为了编辑器能识别，就不写观察者模式了
            preCreater1.createTextInput = () => {
                let preCreater1_ele = creater.createTextInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createNumberInput = () => {
                let preCreater1_ele = creater.createNumberInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createSelect = () => {
                let preCreater1_ele = creater.createSelect(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createMultipleSelect = () => {
                let preCreater1_ele = creater.createMultipleSelect(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }


            preCreater1.createCheckboxInput = () => {
                let preCreater1_ele = creater.createCheckboxInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createEmojiSpanCheckboxInput = () => {
                let preCreater1_ele = creater.createEmojiSpanCheckbox(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createButton = () => {
                let preCreater1_ele = creater.createButton(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createTextArea = () => {
                let preCreater1_ele = creater.createTextArea(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createHideInput = () => {
                let preCreater1_ele = creater.createHideInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createCheckBoxs = () => {
                let preCreater1_ele = creater.createCheckBoxs(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createFileInput = () => {
                let preCreater1_ele = creater.createFileInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createSearchTags = () => {
                let preCreater1_ele = creater.createSearchTags(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createProcessDiv = () => {
                let preCreater1_ele = creater.createProcessDiv(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createWithDroplistTextInput = () => {
                let preCreater1_ele = creater.createWithDroplistTextInput(preCreater1.param);
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }
            preCreater1.createDiv = () => {
                let preCreater1_ele = new Emt('div').setPros({id: creater.getEleRandId('div')});
                preCreater1.__execOnCreate(preCreater1_ele);
                return preCreater1_ele;
            }


            return preCreater1;
        };

        /**
         *
         * @returns preCreator
         */
        creater.preCreate = function (init_param) {
            let tmp = {};
            preCreator.call(tmp, init_param || {});
            return tmp;
        };


        creater.createForm = function (form_input) {
            let form_ele = new Emt('FORM').setAttrsByStr('class="form-horizontal" role="form"', '').addNodes([]);
            form_ele.initConfig = form_input;
            form_ele.initConfig.dataNameTpl = form_input.dataNameTpl || 'name[$var]';
            form_ele.initConfig.labelXsWidth = form_input.labelXsWidth || 2;
            form_ele.initConfig.labelSmWidth = form_input.labelSmWidth || 2;
            form_ele.initConfig.labelMdWidth = form_input.labelMdWidth || 2;
            form_ele.initConfig.labelLgWidth = form_input.labelLgWidth || 2;
            form_ele.initConfig.rowStyle = form_ele.initConfig.rowStyle === undefined ? 'horizontal' : form_ele.initConfig.rowStyle;
            if (form_ele.initConfig.rowStyle === 'horizontal') {
                form_ele.className = 'form-horizontal';
            } else if (form_ele.initConfig.rowStyle === 'no_class') {
                form_ele.className = 'form';
            } else if (form_ele.initConfig.rowStyle === 'inline') {
                form_ele.className = 'form-inline';
            }

            form_ele.libData = {
                group: {},//存放group 的
                input: {}
            };


            form_ele.initInputEle = function (input_ele, ele_index_key, input_name_key) {
                if (typeof ele_index_key === 'string') {
                    root_ele.libData[ele_index_key] = input_ele;
                }

                if (input_name_key) {
                    input_ele.name = init_config.name_tpl.replace('$name_tpl', input_name_key);
                }
                return input_ele;
            };

            /**
             *
             * @param input_ele
             * <br> placeHolder
             * <br> labelText
             * <br> nameVar
             * <br> indexKey
             * @returns {*}
             * @private
             */
            form_ele.__appendGroupInput = function (input_ele, input_param) {
                let group_div = creater.createFormInputGroupDiv({
                    labelXsWidth: input_param.labelXsWidth || form_ele.initConfig.labelXsWidth,
                    labelSmWidth: input_param.labelSmWidth || form_ele.initConfig.labelSmWidth,
                    labelMdWidth: input_param.labelMdWidth || form_ele.initConfig.labelMdWidth,
                    labelLgWidth: input_param.labelLgWidth || form_ele.initConfig.labelLgWidth,
                    helpBlock: input_param.helpBlock,
                    rowStyle: input_param.rowStyle || form_ele.initConfig.rowStyle,

                });
                group_div.addInputEle(input_ele);
                if (!(input_param.keepClass && input_param.keepClass === true)) {
                    input_ele.classList.add("form-control");
                }

                if (input_param.placeHolder !== undefined) {
                    input_ele.placeholder = input_param.placeHolder;
                }
                //console.log(`[${input_param.labelText}]`, input_param.labelText !== undefined);
                if (input_param.labelText !== undefined) {
                    group_div.setText(input_param.labelText);
                }
                if (input_param.detail !== undefined) {
                    group_div.setDetail(input_param.detail);
                }
                if (input_param.nameVar !== undefined) {
                    input_ele.name = form_ele.initConfig.dataNameTpl.replace('$var', input_param.nameVar);
                }
                if (input_param.indexKey) {
                    form_ele.libData.group[input_param.indexKey] = group_div;
                    form_ele.libData.input[input_param.indexKey] = input_ele;
                }
                input_ele.groupDiv = group_div;
                form_ele.addNodes([group_div]);
                return form_ele;
            };


            /**
             * 别被名字迷惑了，本质上还是 creater.preCreate(),只不过在最后包裹了 group div
             * <br>  到 create 中止了!!!!!
             * @returns {preCreator}
             */
            form_ele.presetGroupElement = function (init_param = undefined) {
                init_param = init_param || {};
                //let text_input = creater.createTextInput();
                let pre_create = creater.preCreate(init_param);
                // pre_create 在执行create 的时候会调用
                pre_create.setOnCreate(function (input_ele, input_param) {
                    //console.log(input_ele, input_param);
                    return form_ele.__appendGroupInput(input_ele, input_param);
                });
                return pre_create;
            };
            /**
             *
             * @param input_ele
             * <br> placeHolder
             * <br> labelText
             * <br> nameVar
             * <br> indexKey
             * @returns {*}
             * @private
             */
            form_ele.addTextInput = function (input_param) {
                //let text_input = creater.createTextInput();
                return form_ele.__appendGroupInput(creater.createTextInput(input_param));
            };

            form_ele.addHideInput = function (input_param) {
                return form_ele.__appendGroupInput(creater.createHideInput(input_param));
            };

            form_ele.addNumberInput = function (input_param) {
                return form_ele.__appendGroupInput(creater.createNumberInput(input_param));
            };
            form_ele.addTextArea = function (input_param) {
                return form_ele.__appendGroupInput(creater.createTextArea(input_param));
            };
            form_ele.addSelect = function (input_param) {
                return form_ele.__appendGroupInput(creater.createSelect(input_param));
            };

            form_ele.addFileInput = function (input_param) {
                return form_ele.__appendGroupInput(creater.createFileInput(input_param));
            };

            form_ele.addCheckboxList = function (input_param) {

                let checkbox_list_div = creater.createCheckBoxs(input_param);
                let group_div = creater.createFormInputGroupDiv();
                group_div.addInputEle();
                checkbox_list_div.className.add("form-control");
                if (input_param.labelText) {
                    group_div.setText(input_param.labelText);
                }
                if (input_param.nameVar) {
                    input_ele.name = form_ele.initConfig.nameTpl.replace('$var', input_param.nameVar);
                }
                if (input_param.indexKey) {
                    form_ele.libData.group[input_param.indexKey] = group_div;
                    form_ele.libData.input[input_param.indexKey] = input_ele;
                }
                form_ele.addNodes([group_div]);
                return form_ele;
            };

            /**
             *
             * @param label
             * @param text
             * @param callback  接受俩参数  1:btn本身 2:form
             * @returns {{root_ele}}
             */
            form_ele.addSubmitButton = function (input_param) {
                let btn = new Emt('BUTTON', 'type="button" class="btn btn-default"  ', text).setPros({id: creater.getEleRandId('submit_btn')});
                if (input_param.callback && typeof input_param.callback === 'function') {
                    btn.addEventListener('click', function () {
                        callback(this, form_ele);
                    });
                }
                return form_ele.__appendGroupInput(btn, input_param);
            };


            return form_ele;
        };


        creater.createImg = function (init_configion) {
            let init_config = init_configion || {src: ''};
            let img = new Emt('img').setPros({src: init_config.src || '', id: creater.getEleRandId('img')});

            img.toggleClassName = function (class_name, type) {
                if (type) {
                    if (type === 'add') {
                        img.classList.add(class_name);
                    } else {
                        img.classList.remove(class_name);
                    }
                } else {
                    img.classList.toggle(class_name);
                }
                return img;
            };
            img.toggleRounded = function (type) {
                return img.toggleClassName('img-rounded', type);
            };
            img.toggleCircle = function (type) {
                return img.toggleClassName('img-circle', type);
            };
            img.toggleThumbnail = function (type) {
                return img.toggleClassName('img-thumbnail', type);
            };
            img.toggleResponsive = function (type) {
                return img.toggleClassName('img-responsive', type);
            };

            return img;
        };


        creater.createYesOrNo = function (opt) {
            let option_name = creater.getEleRandId('hammer_input_option');

            let yes_ele = new Emt('INPUT', 'type="radio" value="1" class="hammer_input_option" ', '', {name: option_name});
            let no_ele = new Emt('INPUT', 'type="radio" value="2" class="hammer_input_option"', '', {name: option_name});
            let hide_ele = new Emt('input', 'type="hidden"');
            let inputDiv = new Emt('DIV').addNodes([
                hide_ele,
                new Emt('LABEL').addNodes([
                    yes_ele, new Emt('span', '', '是')
                ]),
                new Emt('LABEL').addNodes([
                    no_ele, new Emt('span', '', '否')
                ])
            ]);
            inputDiv.is_changed = false;
            let updateSelectedVal = function () {
                if (yes_ele.checked === true) {
                    hide_ele.value = yes_ele.value;
                } else if (no_ele.checked === true) {
                    hide_ele.value = no_ele.value;
                } else {
                    return false;
                }
            };

            yes_ele.addEventListener('change', function () {
                inputDiv.is_changed = true;
                updateSelectedVal();
            });
            no_ele.addEventListener('change', function () {
                inputDiv.is_changed = true;
                updateSelectedVal();
            });


            //注意， 如果为null的情况  ele.value!==ele.old_val
            inputDiv.isValChanged = function () {
                return inputDiv.is_changed;
            };
            //注意,这个不准， 如果为null的情况  ele.value!==ele.old_val
            inputDiv.isOldVal = function () {
                return inputDiv.old_val === hide_ele.value;
            };
            inputDiv.getValue = function () {
                return hide_ele.value;
            };

            //可以是[string]，也可以是[{label:xx,val:xx}]
            inputDiv.setNewVal = function (sta_val) {
                let tmp_val = parseInt(sta_val);
                if (tmp_val === 1) {
                    yes_ele.click();
                } else {
                    no_ele.click();
                }

                //inputDiv.value = sta_val;
                inputDiv.old_val = sta_val;//注意， 如果为null的情况  ele.value!==ele.old_val
                inputDiv.is_changed = false;

                return inputDiv;
            };
            if (opt && opt.val) {
                inputDiv.setNewVal(opt.val);
            }

            inputDiv.getVal = function () {
                return hide_ele.value;
            };
            return inputDiv;
        };

        let addStyle = function () {
            if (!kl.id('hammer-bootstarp-style')) {
                document.body.append(
                    new Emt('style').setAttrsByStr(
                        'id="hammer-bootstarp-style"',
                        `                           .hide_btn_sort_asc > .btn_sort_asc {
                                display: none;
                            }
                        
                            .hide_btn_sort_desc > .btn_sort_desc {
                                display: none;
                            }
                        
                            .hide_btn_sort > .btn_sort_asc, .hide_btn_sort > .btn_sort_desc {
                                display: none;
                            }
                        
                            .hammer_input_option {
                                display: none;
                            }
                            input[class="hammer_input_option"] + span {
                                background: #FFF;
                                color: #000;
                            }
                        
                            input[class="hammer_input_option"]:checked + span {
                                background: #000;
                                color: #FFF;
                            }
                            .modal-dialog {
                                width: 80%;
                            }
                            input[type="checkbox"][class="hidden"]:checked + span::after {
                                content: '✔️';
                            }
                    
                           input[type="checkbox"][class="hidden"] + span::after {
                                content: '❌';
                            }
                           .height-auto{
                                height:auto !important;
                           } 
                           .overflow-y-hidden{
                                overflow-y: hidden !important;
                           }
                        `
                    )
                )

            }
        };
        addStyle();

        return creater;
    }
;
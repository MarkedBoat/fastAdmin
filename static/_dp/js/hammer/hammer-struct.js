let inputNode = function (opt, init_val) {
    //console.log(opt.var_path + '->' + opt.key + '->' + 'inputNode', opt);

    if (opt === undefined || opt.inputType === undefined) {
        console.log(opt);
        throw  'inputType_undefined'
    }
    let input_node = new Emt('div', 't="inputNode"');
    input_node.inputEle = false;
    input_node.getVal = function () {
        //console.log('default getVal', inputEle, inputEle.value, opt);
        return input_node.inputEle.value;
    };
    input_node.setVal = function (db_val) {
        //console.log('setVal', db_val);
        input_node.inputEle.value = db_val;
    };
    input_node.addSelectItems = function (list) {
        list.forEach(function (item_info) {
            input_node.inputEle.add(new Option(item_info.text, item_info.val));
        })
    };
    if (['select', 'date_unit'].indexOf(opt.inputType) > -1) {
        input_node.inputEle = new Emt('select');
        if (opt.inputType === 'date_unit') {
            input_node.inputEle.add(new Option('年', 'Y'));
            input_node.inputEle.add(new Option('月', 'm'));
            input_node.inputEle.add(new Option('日', 'd'));
            input_node.inputEle.add(new Option('时', 'H'));
            input_node.inputEle.add(new Option('分', 'i'));
            input_node.inputEle.add(new Option('秒', 's'));

        } else {
            if (opt && opt.vals) {
                input_node.addSelectItems(opt.vals);
            }
        }

    } else if (['bool'].indexOf(opt.inputType) > -1) {
        input_node.inputEle = new Emt('input', 'type="checkbox"');
        input_node.getVal = function () {
            return input_node.inputEle.checked;
        };
        input_node.setVal = function (db_val) {
            //console.log('setVal', db_val);
            if (typeof db_val === "object") throw  1;
            input_node.inputEle.checked = db_val;
        };
    } else if (['textarea'].indexOf(opt.inputType) > -1) {
        input_node.inputEle = new Emt('textarea');
        input_node.getVal = function () {
            return input_node.inputEle.value;
        };
    } else {
        input_node.inputEle = opt.inputType === 'number' ? new Emt('input', 'type="number"') : new Emt('input', 'type="input"');
        input_node.getVal = function () {
            if (input_node.inputEle.type === 'number') {
                let n = parseInt(input_node.inputEle.value);
                return isNaN(n) ? 0 : n;
            } else {
                return input_node.inputEle.value;
            }
        };
    }
    //console.log('xxx', {'key': opt.key, init_val: init_val, 'opt.default': opt.default, 'opt': opt});

    if (input_node.inputEle === false) {
        console.log('不是简单的 input/select能够提供数据的，应该是更复杂的数据类型：', opt, init_val, input_node);
        // throw  '反思一下，为啥没有inputEle';
    } else {
        input_node.addNode(input_node.inputEle);
        if (init_val !== undefined) {
            input_node.setVal(init_val);
        } else {
            console.log('xxx', opt.default, opt);
            input_node.setVal(opt.default);
        }
    }

    input_node.inputEle.addEventListener('change', function () {
        opt.onChanged(input_node.inputEle);
    });
    return input_node;
};


let attrNode = function (opt, init_val) {
    let attr_node = new Emt('div');
    attr_node.dataInfo = opt;
    attr_node.var_path = opt.var_path + '->' + opt.key;
    console.log(attr_node.var_path + '->' + ' INIT attrNode', {input_param: opt, init_val: init_val});
    attr_node.setAttrs({path: attr_node.var_path});

    attr_node.labelEle = new Emt('label', 'class="struct_title"');
    attr_node.labelEle.textContent = opt.title;

    attr_node.addNode(
        new Emt('div').addNode(
            attr_node.labelEle
        )
    );

    attr_node.objectAttrsEle = false;
    attr_node.objectAttrNodes = [];

    attr_node.arrayElementsEle = false;
    attr_node.addArrayElementBtn = false;
    attr_node.addArrayElement = false;


    attr_node.isWorking = true;

    attr_node.valueEle = false;
    if (opt.key === 'root') {
        // opt.root = attr_node;
    }

    if (opt.inputType === 'object') {
        attr_node.classList.add('object_node');
        attr_node.objectAttrsEle = new Emt('div', 'path="object_attrs" class="object_attrs"');
        opt.attrs.forEach((sub_attr_node_config) => {
            sub_attr_node_config.var_path = attr_node.var_path;
            sub_attr_node_config.onChanged = opt.onChanged;
            // console.log('xxx:', sub_attr_node_config, init_val, init_val[sub_attr_node_config.key]);
            //   if (init_val !== undefined && init_val[opt.key] !== undefined && typeof init_val[opt.key] === 'function') {

            let sub_attr_node = new attrNode(sub_attr_node_config, init_val === undefined ? undefined : (init_val[sub_attr_node_config.key] || undefined));
            attr_node.objectAttrsEle.addNode(sub_attr_node);
            attr_node.objectAttrNodes.push(sub_attr_node);
        });
        attr_node.addNodes([
            attr_node.objectAttrsEle,
        ]);

        attr_node.getVal = () => {
            let obj = {};
            attr_node.objectAttrNodes.forEach((sub_attr_node) => {
                obj[sub_attr_node.dataInfo.key] = sub_attr_node.getVal();
            });
            return obj;
        };
    } else if (opt.inputType === 'array') {
        attr_node.classList.add('array_node');
        attr_node.arrayElementsEle = new Emt('div', 'path="array_elements" class="array_elements"');
        attr_node.addArrayElementBtn = new Emt('button', 'type="button"', '添加成员');
        attr_node.addNodes([
            new Emt('div', 'class="array_content_div"').addNodes([
                attr_node.arrayElementsEle,
                new Emt('div', 'class="new_array_element_add_div"').addNodes([
                    new Emt('div', 'class="struct_title"', opt.arrayElementConfig.title),
                    new Emt('div').addNode(attr_node.addArrayElementBtn)
                ]),
            ])
        ]);

        let createArrayElement = (array_element_val) => {
            if (opt.arrayElementConfig.inputType === "object") {
                opt.arrayElementConfig.onChanged = opt.onChanged;
                opt.arrayElementConfig.var_path = attr_node.var_path + '[]';
                console.log('object array:', opt.arrayElementConfig);
                let sub_attr_node = new attrNode(opt.arrayElementConfig, array_element_val);
                attr_node.arrayElementsEle.addNode(sub_attr_node);

            } else if (opt.arrayElementConfig.inputType === "array") {
                console.log('array array:', opt.arrayElementConfig);
                opt.arrayElementConfig.onChanged = opt.onChanged;
                opt.arrayElementConfig.var_path = attr_node.var_path + '[]';
                let sub_attr_node = new attrNode(opt.arrayElementConfig, array_element_val);
                attr_node.arrayElementsEle.addNode(sub_attr_node);
            } else {
                ((sub_attr_node_config) => {
                    sub_attr_node_config.onChanged = opt.onChanged;
                    sub_attr_node_config.var_path = attr_node.var_path + '[]';
                    let sub_attr_node = new attrNode(sub_attr_node_config, array_element_val);
                    attr_node.arrayElementsEle.addNode(sub_attr_node);
                })(opt.arrayElementConfig.attr);
            }

        };
        console.log('array init val ', init_val, '#');
        if (init_val !== undefined && typeof init_val.forEach === 'function') {
            init_val.forEach((array_element_val) => {
                createArrayElement(array_element_val);
            });
        }

        attr_node.getVal = () => {
            let arr = [];
            Object.values(attr_node.arrayElementsEle.childNodes).forEach((sub_attr_node) => {
                arr.push(sub_attr_node.getVal());
            });
            return arr;
        };
        attr_node.addArrayElementBtn.addEventListener('click', function () {
            // createArrayElement({});
            if (opt.arrayElementConfig.inputType === "object") {
                createArrayElement({});
            } else if (opt.arrayElementConfig.inputType === "array") {
                createArrayElement([]);
            } else {
                createArrayElement(opt.arrayElementConfig.attr.default);
            }
        });

    } else {
        attr_node.classList.add('input_node');
        // console.log(init_val, init_val);
        attr_node.valueEle = new inputNode(opt, init_val);
        attr_node.addNode(attr_node.valueEle);

        attr_node.getVal = () => {
            return attr_node.valueEle.getVal();
        };
    }
    return attr_node;

};


let hammerStruct = function (struct_data, db_data, call_function) {
    let struct_div = new Emt('div', 'class="bi_struct"');

    struct_div.lastSetting = {fullStructData: false, infoData: false, callFunction: false};

    struct_div.reload = () => {
        if (struct_div.root_node && typeof struct_div.root_node.remove === "function") {
            struct_div.root_node.remove();
        }
        struct_div.root_node = new attrNode({
            inputType: 'object',
            title: '根',
            attrs: struct_div.lastSetting.fullStructData.struct,
            key: 'root',
            var_path: '',
            onChanged: function (input_ele) {
                console.log('hammerStruct', {inputEle: input_ele});
                if (typeof struct_div.lastSetting.callFunction === "function") {
                    let val = struct_div.getResultInfoData();
                    struct_div.lastSetting.callFunction(val);
                }
            }
        }, struct_div.lastSetting.infoData);
        struct_div.addNode(
            struct_div.root_node
        );
        return struct_div;

    };
    struct_div.loadNewInfoData = (infoData) => {
        if (struct_div.root_node && typeof struct_div.root_node.remove === "function") {
            struct_div.root_node.remove();
            struct_div.lastSetting.infoData = false;
        }
        struct_div.lastSetting.infoData = infoData;
        struct_div.reload();
        return struct_div;
    };
    struct_div.loadNewStructAndInfo = function (fullStructData, infoData, callFunction) {
        if (struct_div.root_node && typeof struct_div.root_node.remove === "function") {
            struct_div.root_node.remove();
            struct_div.lastSetting.infoData = false;
            struct_div.lastSetting.fullStructData = false;
            struct_div.lastSetting.callFunction = false;
        }

        struct_div.lastSetting.infoData = infoData;
        struct_div.lastSetting.fullStructData = fullStructData;
        struct_div.setCallFunction(callFunction);

        struct_div.reload();
        return struct_div;
    };

    struct_div.setCallFunction = (callFunction) => {
        if (typeof callFunction === 'function') {
            struct_div.lastSetting.callFunction = callFunction;
        } else {
            console.log(' struct_div.setCallFunction  参数不是一个function');
            struct_div.lastSetting.callFunction = false;
        }
        return struct_div;
    };
    struct_div.setFullStructData = (fullStructData) => {
        if (typeof fullStructData === 'object' && fullStructData.struct_code !== undefined) {
            struct_div.lastSetting.fullStructData = fullStructData;
        } else {
            console.log(fullStructData);
            throw  ' struct_div.setFullStructData  参数异常';
        }
        return struct_div;
    };
    struct_div.setInfoData = (infoData) => {
        if (typeof infoData === 'object' && infoData.struct_code !== undefined) {
            struct_div.lastSetting.infoData = infoData;
        } else {
            console.log(infoData);
            throw  ' struct_div.setInfoData  参数异常';
        }
        return struct_div;
    };

    struct_div.getResultInfoData = () => {
        if (struct_div.lastSetting.fullStructData === false) {
            throw 'hammer struct 没有相关设置，不能 getInfoData';
        }
        let val = struct_div.root_node.getVal();
        val.struct_code = struct_div.lastSetting.fullStructData.struct_code;
        console.log('hammerStruct inputEle.getVal', val, console.log(JSON.stringify(val, null, 4)));
        console.log("\nval:\n\n\n" + JSON.stringify(val) + "\n\n\n");
        console.log("\nstruct:\n\n\n" + JSON.stringify(struct_div.lastSetting.fullStructData) + "\n\n\n");
        return val;
    };

    if (struct_data !== undefined) {
        let struct_json = JSON.stringify(struct_data);
        console.log("\nstruct:\n\n\n" + struct_json + "\n\n\n", struct_data);
        struct_div.setFullStructData(struct_data);
    }
    if (db_data !== undefined) {
        struct_div.setInfoData(db_data);
    }
    if (typeof call_function === 'function') {
        struct_div.setCallFunction(call_function);
    }

    if (struct_div.lastSetting.fullStructData && struct_div.lastSetting.infoData) {
        struct_div.reload();
    }

    return struct_div;
};
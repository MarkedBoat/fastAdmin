/**
 * Created by markedboat on 2019/12/19.
 */

// Object.prototype.isStdArray = function () {
//     return typeof this.forEach === 'function';
// };


function KL() {
    let self = this;
    self.opt = {log: true};
    self.isset = function (arg) {
        return typeof arg === 'undefined' ? false : true;
    };
    self.id = function (id) {
        return document.getElementById(id);
    };

    self.getValByPath = function (object, keysPath) {
        let keys = keysPath.split('.');
        let last = keys.splice(-1);
        return [object].concat(keys).reduce(function (a, b) {
            if (a[b] === undefined) a[b] = {};
            return a[b];
        })[last] || undefined;
    };
    self.setValByPath = function (object, keysPath, value) {
        let keys = keysPath.split('.');
        let last = keys.splice(-1);
        [object].concat(keys).reduce(function (a, b) {
            if (a[b] === undefined) a[b] = {};
            return a[b];
        })[last] = value;
        return self;
    };

    self.isUndefined = function (baseVar, attr_path) {
        let tmp_ar = attr_path.split('.');
        return tmp_ar.reduce(function (base_var, attr) {
            // console.log(base_var, attr, base_var[attr], 'xxxx');
            return base_var === undefined || base_var === null || typeof base_var[attr] === 'undefined' ? undefined : base_var[attr];
        }, baseVar) === undefined;
    };
    self.xpathSearch = function (xpath, context) {
        let nodes = [];
        try {
            let doc = (context && context.ownerDocument) || window.document;
            let results = doc.evaluate(xpath, context || doc, null, XPathResult.ANY_TYPE, null);
            let node;
            while (node = results.iterateNext()) {
                nodes.push(node);
            }
        } catch (e) {
            throw e;
        }
        return nodes;
    };
    /**
     * json 解码
     * <br>!!!只要原参数是 object ，不会检查是不是数组
     * @param sourceData
     * @param defaultValue
     * @returns {{}|any}
     */
    self.jsonDecode = function (sourceData, defaultValue) {
        if (sourceData === null || sourceData === undefined) {
            return defaultValue;
        }
        let sourceDataType = typeof sourceData;
        let res;
        if (sourceDataType === 'string') {
            try {
                res = JSON.parse(sourceData);
                return res;
            } catch (e) {
                return defaultValue;
            }
        } else {
            if (sourceDataType === 'object') {
                return sourceData;
            }
            return defaultValue;
        }
    };


    self.getCookie = function (cookie_name) {
        let cks = document.cookie.split(';');
        for (let i = 0; i < cks.length; i++) {
            if (cks[i].search(cookie_name) !== -1) {
                return decodeURIComponent(cks[i].replace(cookie_name + '=', ''));
            }
        }
    };

    self.setCookie = function (name, val, day, domain) {
        let date = new Date();
        date.setTime(date.getTime() + day * 24 * 3600 * 1000);
        let time_out = date.toGMTString();
        //console.log(time_out, val);
        document.cookie = name + '=' + encodeURIComponent(val) + ';expires=' + time_out + ';path=/;domain=' + domain;
    };
    /**
     * 将多维 object 转化成 from的key=>name
     * @param fromData
     * @param input_data
     * @param level
     * @param name_root
     */
    self.data2form = function (fromData, input_data, level, name_root) {
        if (level === 0) {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    self.data2form(fromData, input_data[k], 1, k);
                } else {
                    fromData.append(k, input_data[k]);
                }
            }
        } else {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    self.data2form(fromData, input_data[k], level + 1, name_root + '[' + k + ']');
                } else {
                    fromData.append(name_root + '[' + k + ']', input_data[k]);
                }
            }
        }
    };

    /**
     * 将多维 object 转化成 from的key=>name
     * @param dstList
     * @param input_data
     * @param level
     * @param name_root
     */
    self.data2list = function (dstList, input_data, level, name_root) {
        if (level === 0) {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    self.data2list(dstList, input_data[k], 1, k);
                } else {
                    dstList.push({key: k, val: input_data[k]});
                }
            }
        } else {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    self.data2list(dstList, input_data[k], level + 1, name_root + '[' + k + ']');
                } else {
                    dstList.push({key: name_root + '[' + k + ']', val: input_data[k]});
                }
            }
        }
    };


    /**
     *
     * @param opts
     */
    self.ajax = function (opts) {
        let request = new XMLHttpRequest();
        opts.httpOkCodes = opts.httpOkCodes || [];
        request.timeout = (opts.timeout || 30) * 1000;
        request.responseType = opts.responseType || request.responseType;
        if (opts.async !== true) {
            request.addEventListener("load", function () {
                if (typeof opts.onload === 'function') {
                    opts.onload(request);
                } else {
                    if (request.status == 200 || opts.httpOkCodes.indexOf(request.status) !== -1) {
                        let result = request.responseText;
                        if (opts.type === 'json') {
                            try {
                                result = JSON.parse(request.responseText);
                            } catch (e) {
                                if (opts.error) {
                                    opts.error('请求结果不能保存为 json');
                                }
                            }
                        }
                        opts.success(result);
                    } else {
                        if (opts.error) {
                            opts.error(request.status + ':' + request.statusText);
                        }
                    }
                }

            }, false);
        }

        request.addEventListener("timeout", function () {
            console.log('出错了');
            if (opts.error) opts.error(request.statusText, 'timeout');
        }, false);

        request.addEventListener("error", function () {
            console.log('出错了');
            if (opts.error) opts.error(request.statusText, 'error');
        }, false);

        request.addEventListener("abort", function () {
            console.log('中断了');
            if (opts.error) opts.error(request.statusText, 'abort');
        }, false);

        if (opts.progress) {
            request.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    opts.progress(evt.loaded, evt.total);
                }
            }, false);
        }


        //request.onreadystatechange = requestCallback;
        request.open((opts.method || "POST"), opts.url, true);
        if (opts.isAjax !== false) request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        //request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        if (opts.headers && typeof opts.headers.forEach === 'function') {
            opts.headers.forEach((header_ar) => {
                request.setRequestHeader(header_ar[0], header_ar[1]);
            });
        }

        opts.form = opts.form || new FormData();

        if (opts.data) {
            self.data2form(opts.form, opts.data, 0, '');
        }

        if (opts.async === true) {
            return new Promise(function (resolve, reject) {
                request.send(opts.form);
                request.onload = function () {
                    if (request.status === 200 || opts.httpOkCodes.indexOf(request.status) !== -1) {
                        let result = request.responseText;
                        if (opts.type === 'json') {
                            try {
                                result = JSON.parse(request.responseText);
                            } catch (e) {
                                return resolve({isOk: false, msg: 'json结构异常', request: {status: request.status, statusText: request.statusText, responseText: request.responseText}});
                            }
                        }
                        //return resolve({isOk: true, result: result});
                        return resolve({isOk: true, result: result, request: {status: request.status, statusText: request.statusText, responseText: request.responseText}});
                    } else {
                        return resolve({isOk: false, msg: '请求异常', request: {status: request.status, statusText: request.statusText, responseText: request.responseText}});
                        //return reject(request.status + ':' + request.statusText);
                    }
                }
            });
        } else {
            request.send(opts.form);
        }
        return request;
    };
    self.getStack = function () {
        //    console.log.apply(function(){},arguments)
        return new Error().stack.replace('Error', 'Stack');
    };

    self.log = ((self.opt.log === true) || window.localStorage.getItem('hammer_opt_log') === 'on') && console && console.log ? console.log : function () {
    };

    return self;
}


/**
 *
 * @param tagName
 * @param attrsStr
 * @param textContent
 * @param prototypeMap
 * @returns {HTMLElement }
 * @constructor
 */
function Emt(tagName, attrsStr, textContent, prototypeMap) {
    let ele = document.createElement(tagName);
    if (typeof attrsStr === 'string') {
        ele.setAttrsByStr(attrsStr, textContent || '');
    }
    if (typeof prototypeMap === 'object') {
        ele.setPros(prototypeMap);
    }
    return ele;
}

HTMLElement.prototype.addNode = function () {
    for (let i = 0; i < arguments.length; i++) {
        if (typeof arguments[i] !== 'string') {
            this.appendChild(arguments[i]);
            arguments[i].boss = this;
            arguments[i].parent = this;
            if (typeof arguments[i + 1] === 'string') {
                if (arguments[i + 1]) this[arguments[i + 1]] = arguments[i];
            }
        }
    }
    return this;
};

HTMLElement.prototype.addNodes = function (nodes) {
    for (let i in nodes) {
        let node = nodes[i];
        if (typeof node === 'string') {
            this.innerHTML += node;
        } else if (node === false) {
            //
        } else {
            nodes.boss = this;
            this.appendChild(node);
            (node.eleParent || this)[node.eleName || i] = node;
        }
    }
    return this;
};

HTMLElement.prototype.setStyle = function (configs) {
    for (let attr in configs) {
        this.style[attr] = configs[attr];
    }
    return this;
};
HTMLElement.prototype.setPros = function (configs) {
    for (let attr in configs) {
        this[attr] = configs[attr];
    }
    return this;
};
/**
 * 设置句柄及索引
 * @param {Object} index_handler
 * @param index_name
 * @returns {HTMLElement}
 */
HTMLElement.prototype.setIndexHandler = function (index_handler, index_name) {
    index_handler[index_name] = this;
    this.indexHandler = index_handler;
    return this;
};
HTMLElement.prototype.setAttrs = function (configs, isAddPrototype) {
    for (let attr in configs) {
        this.setAttribute(attr, configs[attr]);
    }
    if (isAddPrototype) {
        for (let attr in configs) {
            this[attr] = configs[attr];
        }
    }
    return this;
};
//必须是双引号的
HTMLElement.prototype.setAttrsByStr = function (raw_attrs_str, textContent) {
    let tmp_ar = raw_attrs_str.replace(/=\s?\"\s?/g, '=').replace(/\"\s+/g, '" ').replace(/\s?\:\s?/g, ':').split('" ');
    for (let ar_i = 0; ar_i < tmp_ar.length; ar_i++) {
        let tmp_str = tmp_ar[ar_i];
        let tmp_ar2 = tmp_str.split('=');
        if (tmp_ar2.length === 2) {
            this.setAttribute(tmp_ar2[0].replace(/\s/g, ''), tmp_ar2[1].replace(/(^\s)|(\s$)|"/g, ''));
        }
    }
    if (typeof textContent === 'string') {
        this.textContent = textContent;
    }
    return this;
};

HTMLElement.prototype.setEventListener = function (event, fn) {
    this.addEventListener(event, fn);
    return this;
};
HTMLElement.prototype.bindEvent = function (event, fn) {
    this.addEventListener(event, fn);
    return this;
};

/**
 *
 * @param opts
 let opts = {
            path: 'premit.startTime',
            domData: domData
         }
 * @returns {HTMLElement}
 */
HTMLElement.prototype.bindData = function (opts) {
    opts.ele = this;
    opts.domData.bindData(opts);
    return this;
};


HTMLElement.prototype.toggleClassList = function (class_name, is_add) {
    if (typeof is_add === 'undefined') {
        this.classList.toggle(class_name);
    } else if (is_add) {
        this.classList.add(class_name);
    } else {
        this.classList.remove(class_name);
    }
    return this;
};

HTMLElement.prototype.select_item_vals = [];
HTMLElement.prototype.select_item_eles = [];

HTMLElement.prototype.addSelectItem = function (val, text, is_default) {
    let self = this;
    if (self.tagName === 'SELECT') {
        if (self.select_item_vals.indexOf(val) === -1) {
            self.select_item_vals.push(val);
            let opt = new Option(text, val);
            opt.is_default = is_default;
            opt.val = val;
            self.select_item_eles.push();
            self.add(opt);
            if (is_default) {
                self.value = val;
            }
        }
    } else {
        console.log('调用错误，非select 不能使用 addSelectItem 方法');
    }
    return self;
};
/**
 *
 * @param list [ {val:xx,text:xx,is_default:true/false} ]
 * @returns {HTMLElement}
 */
HTMLElement.prototype.addSelectItemList = function (list) {
    let self = this;
    if (typeof list.forEach === 'function') {
        list.forEach(function (info) {
            self.addSelectItem(info.val || '', info.text || '', info.is_default || '')
        });
    }
    return this;
};
HTMLElement.prototype.clearSelectItems = function (keep_dafault) {
    let index0 = 0;
    for (let i in self.select_item_eles) {
        if (keep_dafault === true && self.select_item_eles[index0].is_default === true) {
            console.log('保留', index0, self.select_item_eles[index0]);
            index0 = index0 + 1;
        }
        self.select_item_eles[index0].remove();
    }
    if (self.select_item_eles.length > 0) {
        if (keep_dafault === true) {
            self.select_item_vals = [self.select_item_eles[0].val];
        } else {
            self.select_item_vals = [];
        }
    }

};


function domLoaded(fn) {
    document.addEventListener('DOMContentLoaded', function () {
        console.log('ready 1');
        fn();
    });
}

if (window.kl === undefined) {
    window.kl = new KL();
}


console.log('loaded hammer.js');
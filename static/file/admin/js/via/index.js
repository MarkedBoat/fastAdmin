/*
 * @name: 刻舟自用插件
 * @Author: markedboat
 * @version: 0.2022.02.12.0
 * @description: 抓取收藏功能
 * @include: 暂时不限定看情况
 * @createTime: 2022.02.12
 * @updateTime: 2022.02.12
 */
(function () {


    //alert(':' + window.location.hostname);
    /* 遇到这些网站才执行 */
    var whiteList = ['zhihu.com', 'www.zhihu.com'];

    if (whiteList.indexOf(window.location.hostname) < 0) {
        console.log('host out:' + window.location.hostname);
        return;
    }


    if (window.markedboat_via_index) {
        console.log('markedboat_via_index:', markedboat_via_index);
        return;
    }

    window.markedboat_via_index = true;

    /* 这里写你的代码 */


    // Object.prototype.isStdArray = function () {
//     return typeof this.forEach === 'function';
// };
    let KL = function () {
        var self = this;
        self.isset = function (arg) {
            return typeof arg === 'undefined' ? false : true;
        };
        self.id = function (id) {
            return document.getElementById(id);
        };
        self.isUndefined = function (baseVar, attr_path) {
            let tmp_ar = attr_path.split('.');
            return tmp_ar.reduce(function (base_var, attr) {
                // console.log(base_var, attr, base_var[attr], 'xxxx');
                return base_var === undefined || typeof base_var[attr] === 'undefined' ? undefined : base_var[attr];
            }, baseVar) === undefined;
        };
        self.xpathSearch = function (xpath, context) {
            var nodes = [];
            try {
                var doc = (context && context.ownerDocument) || window.document;
                var results = doc.evaluate(xpath, context || doc, null, XPathResult.ANY_TYPE, null);
                var node;
                while (node = results.iterateNext()) {
                    nodes.push(node);
                }
            } catch (e) {
                throw e;
            }
            return nodes;
        };


        self.ajax = function (opts) {
            var request = new XMLHttpRequest();
            opts.httpOkCodes = opts.httpOkCodes || [];
            request.timeout = (opts.timeout || 30) * 1000;
            request.addEventListener("load", function () {
                if (request.status == 200 || opts.httpOkCodes.indexOf(request.status) !== -1) {
                    var result = request.responseText;
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
            }, false);
            request.addEventListener("error", function () {
                console.log('出错了');
                if (opts.error) opts.error('出错了', 'error');
            }, false);
            request.addEventListener("abort", function () {
                console.log('中断了');
                if (opts.error) opts.error('中断了', 'abort');
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
            if (opts.form) {
                request.send(opts.form);
            } else {
                var fromData = new FormData();
                for (var k in opts.data) {
                    if (typeof opts.data[k] === 'object') {
                        for (var k2 in opts.data[k]) {
                            if (typeof opts.data[k][k2] === 'object') {
                                for (var k3 in opts.data[k][k2]) {
                                    if (typeof opts.data[k][k2][k3] === 'object') {
                                        for (var k4 in opts.data[k][k2][k3]) {
                                            fromData.append(k + '[' + k2 + '][' + k3 + '][' + k4 + ']', opts.data[k][k2][k3][k4]);//就不写递归 哈哈
                                        }
                                    } else {
                                        fromData.append(k + '[' + k2 + '][' + k3 + ']', opts.data[k][k2][k3]);
                                    }
                                }
                            } else {
                                fromData.append(k + '[' + k2 + ']', opts.data[k][k2]);
                            }
                        }
                    } else {
                        fromData.append(k, opts.data[k]);
                    }
                }
                request.send(fromData);
            }

        }

        self.log = window.localStorage.getItem('hammer_opt_log') === 'on' && console && console.log ? console.log : function () {
        };
        return self;
    };

    var kl = new KL();
    var Emt = function (tagName, attrsStr, textContent, prototypeMap) {
        var tmp = document.createElement(tagName);
        Elmt.call(tmp);
        //t.prototype=new Elmt();
        if (typeof attrsStr === 'string') {
            tmp.setAttrsByStr(attrsStr, textContent || '');
        }
        if (typeof prototypeMap === 'object') {
            tmp.setPros(prototypeMap)
        }
        return tmp;
    };
    var Elmt = function (tag) {
        // Elmt.prototype=new Emt(tag);
        var self = this;
        self.setStyle = function (configs) {
            for (var attr in configs)
                self.style[attr] = configs[attr];
            return self;
        };
        self.setPros = function (configs) {
            for (var attr in configs)
                self[attr] = configs[attr];
            return self;
        };
        /**
         * 设置句柄及索引
         * @param index_handler
         * @param index_name
         * @returns {Elmt}
         */
        self.setIndexHandler = function (index_handler, index_name) {
            index_handler[index_name] = self;
            self.indexHandler = index_handler;
            return self;
        };
        self.setAttrs = function (configs, isAddPrototype) {
            for (var attr in configs)
                self.setAttribute(attr, configs[attr]);
            if (isAddPrototype) for (var attr in configs)
                self[attr] = configs[attr];
            return self;
        };
        //必须是双引号的
        self.setAttrsByStr = function (raw_attrs_str, textContent) {
            var tmp_ar = raw_attrs_str.replace(/=\s?\"\s?/g, '=').replace(/\"\s+/g, '" ').replace(/\s?\:\s?/g, ':').split('" ');
            tmp_ar.forEach(function (tmp_str) {
                var tmp_ar2 = tmp_str.split('=');
                if (tmp_ar2.length === 2) {
                    self.setAttribute(tmp_ar2[0].replace(/\s/g, ''), tmp_ar2[1].replace(/(^\s)|(\s$)|"/g, ''));
                }
            });
            if (typeof textContent === 'string') {
                self.textContent = textContent;
            }
            return self;
        };
        self.setEventListener = function (event, fn) {
            self.addEventListener(event, fn);
            return self;
        };
        self.bindEvent = function (event, fn) {
            self.addEventListener(event, fn);
            return self;
        };
        /**
         *
         * @param opts
         var opts = {
            path: 'premit.startTime',
            domData: domData
         }
         * @returns {Elmt}
         */
        self.bindData = function (opts) {
            opts.ele = self;
            opts.domData.bindData(opts);
            return self;
        };

        self.addNode = function () {
            for (var i = 0; i < arguments.length; i++) {
                if (typeof arguments[i] !== 'string') {
                    self.appendChild(arguments[i]);
                    arguments[i].boss = self;
                    arguments[i].parent = self;
                    if (typeof arguments[i + 1] === 'string') {
                        if (arguments[i + 1]) self[arguments[i + 1]] = arguments[i];
                    }
                }
            }
            return self;
        };
        self.addNodes = function (nodes) {
            for (var i in nodes) {
                var node = nodes[i];
                if (typeof node === 'string') {
                    self.innerHTML += node;
                } else {
                    nodes.boss = self;
                    self.appendChild(node);
                    (node.eleParent || self)[node.eleName || i] = node;
                }

            }
            return self;
        };
        self.toggleClassList = function (class_name, is_add) {
            if (typeof is_add === 'undefined') {
                self.classList.toggle(class_name);
            } else if (is_add) {
                self.classList.add(class_name);
            } else {
                self.classList.remove(class_name);
            }
            return self;
        };


        self.select_item_vals = [];
        self.select_item_eles = [];

        self.addSelectItem = function (val, text, is_default) {
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
        };

        /**
         *
         * @param list [ {val:xx,text:xx,is_default:true/false} ]
         * @returns {Elmt}
         */
        self.addSelectItemList = function (list) {
            if (typeof list.forEach === 'function') {
                list.forEach(function (info) {
                    self.addSelectItem(info.val || '', info.text || '', info.is_default || '')
                });
            }
            return self;
        };
        self.clearSelectItems = function (keep_dafault) {
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


        return self;
    };



    function domLoaded(fn) {
        document.addEventListener('DOMContentLoaded', function () {
            console.log('ready 1');
            fn();
        });
    }


// //想用加载后写入本地，还是绕不开安全策略，还是把代码拿过来吧
    // ajax2({
    //     url: 'https://www.markedboat.com/cors/js_file?file=/hammer/kl-hammer.js',
    //     data: {file: '/hammer/kl-hammer.js'},
    //     method: 'POST',
    //     success: function (res) {
    //         let js_script = document.createElement('script');
    //         js_script.innerHTML = res;
    //         document.body.append(js_script);
    //
    //     },
    //     isAjax: false
    // })

    let divs = document.getElementsByClassName('ContentItem-meta');
    for (let i = 0; i < divs.length; i++) {
        let btn = new Emt('button', 'type="button" style="padding:10px;border:1px solid #000;"', '收藏');
        divs[i].append(btn);
        let current_div = divs[i];
        btn.addEventListener('click', function () {
            console.log(current_div);
            let answer_info = {};
            try {
                answer_info = JSON.parse(current_div.parentElement.getAttribute('data-zop'));
            } catch (e) {
                alert('获取失败');
                return false;
            }

            kl.ajax({
                url: 'https://markedboat.com/cors/zhihu',
                data: {
                    author: answer_info.authorName,
                    title: answer_info.title,
                    content: current_div.parentElement.getElementsByClassName('RichText ')[0].innerHTML,
                    link: document.location.href,
                    type: 'answer'
                },
                success: function (res) {
                    console.log(res);
                    if (res && res.status) {
                        console.log(res.status, res.status === 200, res.data);
                        if (res.status === 200) {
                            alert('成功');
                        } else {
                            alert(res.msg || '错误 请检查数据结构');
                        }

                    } else {
                        alert('服务异常');
                    }
                },
                isAjax: false,
                type: 'json'
            });
        });
    }


    let video_divs = document.getElementsByClassName('ZVideo-mainColumn');
    for (let i = 0; i < video_divs.length; i++) {
        let btn = new Emt('button', 'type="button" style="padding:10px;border:1px solid #000;"', '收藏');
        let current_div = video_divs[i];
        let title_emts = current_div.getElementsByClassName('ZVideo-title');
        let detail_emts = current_div.getElementsByClassName('ZVideo-description');
        if (!(title_emts.length > 0 && detail_emts.length > 0 && title_emts[0].textContent.length > 0 && detail_emts[0].textContent.length > 0)) {
            console.log(title_emts[0],detail_emts[0]);
            alert('获取不到视频描述信息');
            continue;
        }
        title_emts[0].append(btn);
        btn.addEventListener('click', function () {
            console.log(current_div);

            let tag_names = [];
            let tag_emts = document.getElementsByClassName('ZVideoTag');
            for (let j = 0; j < tag_emts.length; j++) {
                tag_names.push(tag_emts[j].textContent);
            }
            tag_names = tag_names.join(' ').substring(0, 64);

            kl.ajax({
                url: 'https://markedboat.com/cors/zhihu',
                data: {
                    author: '#',
                    title: title_emts[0].textContent,
                    content: detail_emts[0].textContent,
                    link: document.location.href,
                    tag_names: tag_names,
                    type: 'video'
                },
                success: function (res) {
                    console.log(res);
                    if (res && res.status) {
                        console.log(res.status, res.status === 200, res.data);
                        if (res.status === 200) {
                            alert('成功');
                        } else {
                            alert(res.msg || '错误 请检查数据结构');
                        }

                    } else {
                        alert('服务异常');
                    }
                },
                isAjax: false,
                type: 'json'
            });
        });
    }


})();


// try{
//     var js_script=document.createElement('script');
//     js_script.src='https://www.markedboat.com/static/js/via/index.js?t='+Math.random();
//     document.body.append(js_script);
//     js_script.onerror=function(){
//         alert('js error');
//     };
//     js_script.onload=function(){
//         alert('js onload');
//     };
// }catch (e) {
//     alert('js插入失败');
// }

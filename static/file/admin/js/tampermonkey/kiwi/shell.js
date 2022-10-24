// ==UserScript==
// @name         Kiwi插件
// @namespace    http://tampermonkey.net/
// @version      0.20220403.2043
// @description  try to take over the world!
// @author       You
// @match        *://*/*
// @include      chrome:*
// @include      kiwi:*
// @include      chrome://new-tab-page-third-party/
// @include      http*://*chrome/newtab*
// @grant        unsafeWindow
// @grant        GM_addElement
// @grant        GM_xmlhttpRequest
// @grant        GM_getResourceText
// @grant window.close
// @grant window.focus
// @connect      https://markedboat.com/cors/*
// @updateURL    https://markedboat.com/static/js/tampermonkey/kiwi/shell.js
// @downloadURL    https://markedboat.com/static/js/tampermonkey/kiwi/shell.js
// @run-at document-start
// ==/UserScript==

(function () {
    'use strict';
    let record_xhr = true;
    setInterval(function () {
        record_xhr = localStorage.getItem('record_xhr') === 'true';
    }, 100);
    let rand_str = Math.random().toString().replace(/\./i, '');
    window.setTimeout(function () {
        console.log(((self == top) ? 'top' : 'iframe') + (':' + document.location.href) + "\n" + '都15秒了，不能把浏览器写炸了,清理 kiwi_js_xhr_record_data,为了保险，延迟10秒');//定时器的number 会重复，所以改用随机数
        console.log('kl_kiwi_js_xhr_record_data_default_cleaner', localStorage.getItem('kl_kiwi_js_xhr_record_data_default_cleaner'));

        if (localStorage.getItem('kl_kiwi_js_xhr_record_data_default_cleaner') === 'false') {
            console.log('kl_kiwi_js_xhr_record_data_default_cleaner === false ,不清理');
            //localStorage.removeItem('kiwi_js_xhr_record_data_cleaner');
            return false;
        } else {
            console.log('kl_kiwi_js_xhr_record_data_default_cleaner !== false ,清理！！！');
        }
        localStorage.setItem('record_xhr', 'false');
        record_xhr = false;
        setTimeout(function () {
            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i); //获取本地存储的Key
                if (key.indexOf('kiwi_js_xhr_') === 0) {
                    localStorage.removeItem(key);
                }
            }
        }, 10 * 1000);
    }, 15 * 1000);
    if (!localStorage.getItem('kl_kiwi_js_xhr_record_data_default_cleaner')) {
        localStorage.setItem('kl_kiwi_js_xhr_record_data_default_cleaner', 'true');
    }

    XMLHttpRequest.prototype._url = '';
    if (XMLHttpRequest.prototype.realOpen3 === undefined) {
        XMLHttpRequest.prototype.realOpen3 = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function (method, url, sync) {
            this._url = url;
            this.realOpen3(method, url, sync);
        };

        XMLHttpRequest.prototype.realSend = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function (value) {
            this.addEventListener("load", function () {
                if (record_xhr) {
                    console.log("\n----------------------------------\nrecord_XHR\n", this.responseURL,);
                    window.localStorage.setItem('kiwi_js_xhr_' + this.responseURL.replace(/http[s]?:\/\/(.*?)\/(.*)?\?(.*)?/, '$2').replace(/[:\/.]/ig, '').toString(), this.responseURL + '[kiwi_js_xhr_]' + this.responseText);
                }
            }, false);
            this.realSend(value);
        };

    }

    if (self != top) {
        console.log('iframe中,上面的需要记录xhr,不重新加载不行，但是下面的大可不必', document.location.href);
        return false;//如果在ifrmae中，啥都不要干
    }

    let injectJs = function () {
        console.log('js 注入总成 开始指定网站');
        let require_js_names = ['hammer/kl-hammer.js', 'tampermonkey/kiwi/index.js'];
        let js_names = [];
        let exist_js_names = [];
        let base_url = 'https://markedboat.com/cors/js_file?src=kiwi&host=' + document.location.host + '&file=';//为了cors header，所以让php转一下
        //localStorage.setItem('close_current_window', 'false');

        if (typeof KL !== 'undefined') {
            exist_js_names.push('hammer/kl-hammer.js');
        }
        require_js_names.forEach(function (js_name) {
            if (exist_js_names.indexOf(js_name) === -1) {
                js_names.push(js_name);
            }
        });
        console.log('get js:', js_names);
        let ajaxGet = function (opts) {
            let request = new XMLHttpRequest();
            opts.httpOkCodes = opts.httpOkCodes || [];
            request.timeout = (opts.timeout || 30) * 1000;
            request._skip_lock = true;
            request.addEventListener("load", function () {
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
            request.realSend();

        };

        try {
            ajaxGet({
                url: base_url + js_names.join(','),
                data: {},
                method: 'GET',
                isAjax: false,
                success: function (res) {
                    if (res.length > 1000) {
                        GM_addElement(document.body, 'script', {
                            textContent: 'let alertMsg=function(msg){kl.log(msg);alert(msg);};' + "\n" + res
                        });
                    } else {
                        console.log(res);
                    }
                },
                error: function (error) {
                    alert(error);
                }
            });
        } catch (e) {
            alert(e.message);
        }

        if (0) {
            GM_xmlhttpRequest({
                method: "get",
                url: base_url + js_names.join(','),
                data: {},
                onload: function (res) {
                    if (res.responseText.length > 1000) {
                        GM_addElement(document.body, 'script', {
                            textContent: "\n" + res.responseText
                        });
                    } else {
                        console.log(res.responseText);
                    }
                },
                onerror: function (error) {
                    console.log('error', error);
                },
                onabort: function (onabort) {
                    console.log('onabort ', onabort);
                }
            });
        }

        // document.body.append(js);// 有些网站csp 同源策略太严格了，只能用上面的GM绕开
    };
    if (0) {

        if (navigator.userAgent.indexOf('JmGO') !== -1) {
            // 兼容各大浏览器
            let hiddenProperty = 'hidden' in document ? 'hidden' : 'webkitHidden' in document ? 'webkitHidden' : 'mozHidden' in document ? 'mozHidden' : null;
            let visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
// 监听浏览器的窗口改变事件  document.hidden 事件 false 就是没被隐藏 true 就是被隐藏
            let onVisibilityChange = function () {
                if (document[hiddenProperty]) {
                    //console.log('窗口被切换了');
                    window.close();//确认是 TV 再执行这个
                } else {
                    //console.log('窗口切换回来了');
                }
            };
            document.addEventListener(visibilityChangeEvent, onVisibilityChange);
        }
    }
    window.localStorage.setItem('close_current_window', 'false');
    setInterval(function () {
        if (window.localStorage.getItem('close_current_window') === 'true') {
            window.close();
        }
    }, 200);

    console.log('tampermonkey index 检查 typeof KL ', typeof KL);

    injectJs();


})();
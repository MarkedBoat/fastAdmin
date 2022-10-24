// ==UserScript==
// @name         Markedboat插件总成
// @namespace    http://tampermonkey.net/
// @version      0.20220328.01.18
// @description  try to take over the world!
// @author       You
// @match        *://*/*
// @grant        unsafeWindow
// @grant        GM_addElement
// @grant        GM_xmlhttpRequest
// @grant        GM_getResourceText
// @resource hammerjs https://markedboat.com/static/tmp7415/js/hammer/kl-hammer.js
// @updateURL    https://markedboat.com/static/js/tampermonkey/index.js
// @downloadURL    https://markedboat.com/static/js/tampermonkey/index.js
// ==/UserScript==

(function () {
    'use strict';
    let hammerjs = GM_getResourceText('hammerjs');

    let randStr = Math.random().toString().substr(3, 5);
    let injectJs = function () {
        console.log('js 注入总成 开始指定网站');
        let js = document.createElement('script');

        switch (document.location.host) {
            case 'www.sehuatang.net':
                js.src = 'https://markedboat.com/static/tmp' + randStr + '/js/tampermonkey/av/sehuatang/index.js';
                break;
            case 'www.zhihu.com':
            case 'zhuanlan.zhihu.com':
                //js.src = 'https://markedboat.com/static/tmp' + randStr + '/js/tampermonkey/collection/zhihu/index.js';
                js.src = 'https://markedboat.com/cors/js_file?file=tampermonkey/collection/zhihu/index.js';//为了cors header，所以让php转一下
                break;
            case 'www.aixdzs.com':
                js.src = 'https://markedboat.com/static/tmp' + randStr + '/js/tampermonkey/novel/aixdzs.com/index.js';
                break;

        }
        //console.log('GM_getResourceText', hammerjs);
        if (0) {
            GM_xmlhttpRequest({
                method: "get",
                url: js.src,
                data: {},
                onload: function (res) {
                    console.log(res);
                    GM_addElement(document.body, 'script', {
                        textContent: hammerjs + "\n" + res.responseText
                    });
                },
                onerror: function (error) {
                    console.log('error', error);
                },
                onabort: function (onabort) {
                    console.log('onabort ', onabort);
                }
            });
        } else {
            GM_addElement(document.body, 'script', {
                textContent: hammerjs
            });
            console.log(js.src);
            kl.ajax({
                url: js.src,
                data: {},
                method: 'GET',
                isAjax: false,
                success: function (res) {
                    console.log(res);
                    GM_addElement(document.body, 'script', {
                        textContent: 'let alertMsg=function(msg){kl.log(msg);alert(msg);}' + "\n" + res
                    });
                },
                onerror: function (error) {
                    console.log('error', error);
                }
            });
        }

        // document.body.append(js);// 有些网站csp 同源策略太严格了，只能用上面的GM绕开

    };

    console.log('tampermonkey index 检查 typeof KL ', typeof KL);

    injectJs();


})();
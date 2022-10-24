// ==UserScript==
// @name         Markedboat插件总成
// @namespace    http://tampermonkey.net/
// @version      0.20220417.8
// @description  总览各个插件
// @author       ahyjl@126.com
// @match        *://*/*
// @grant        unsafeWindow
// @grant        GM_addElement
// @grant        GM_xmlhttpRequest
// run-at document-start
// @updateURL    https://markedboat.com/cors/js_file?file=tampermonkey/index.js
// @downloadURL    https://markedboat.com/cors/js_file?file=tampermonkey/index.js
// ==/UserScript==

(function () {
    'use strict';

    let simpleAjax = function (opts) {
        let request = new XMLHttpRequest();
        request.timeout = (opts.timeout || 30) * 1000;
        request.addEventListener("load", function () {
            if (request.status == 200 || opts.httpOkCodes.indexOf(request.status) !== -1) {
                let result = request.responseText;
                opts.success(result);
            } else {
                console.log(request.status + ':' + request.statusText);
            }
        }, false);
        request.addEventListener("error", function () {
            console.log('ajax出错了',request);
        }, false);
        request.addEventListener("abort", function () {
            console.log('ajax中断了',request);
        }, false);
        request.open('GET', opts.url, true);
        //request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        request.send();

    };

    let base_url = 'https://markedboat.com/cors/assembly?src=assembly&host=' + document.location.host + '&url=' + document.location.href ;
    simpleAjax({
        url: base_url,
        success: function (res) {
            console.log(res);
            GM_addElement(document.body, 'script', {
                textContent: res + "\n" + "\n",
            });
        },
    });


    // document.body.append(js);// 有些网站csp 同源策略太严格了，只能用上面的GM绕开

    console.log('tampermonkey index 检查 typeof KL ', typeof KL);


})();
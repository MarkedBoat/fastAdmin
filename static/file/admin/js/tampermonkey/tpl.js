/**
 * 作为模板
 * @type {HTMLScriptElement}
 */
if (typeof KL !== 'function') {
    let js = document.createElement('script');
    js.src = 'https://markedboat.com/static/tmp' + Math.random().toString().substr(3, 10) + '/js/hammer/kl-hammer.js';

    js.addEventListener('load', function () {
        init();
    });
    document.body.append(js);
} else {
    init();
}

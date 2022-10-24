kl.download_urls = [];

function downloadFile2(ele, file_url, fileName) {
    if (kl.download_urls.indexOf(file_url) !== -1) {
        alert('切勿重试');
        return false;
    }
    let tmp_index = kl.download_urls.length;
    let raw_textcontent = ele.textContent;
    kl.download_urls.push(file_url);
    let x = new XMLHttpRequest();
    x.open("GET", file_url, true);
    x.responseType = 'blob';
    x.onload = function (e) {
        kl.download_urls[tmp_index] = '#';
        console.log('ok', file_url);
        //会创建一个 DOMString，其中包含一个表示参数中给出的对象的URL。这个 URL 的生命周期和创建它的窗口中的 document 绑定。这个新的URL 对象表示指定的 File 对象或 Blob 对象。
        let url = window.URL.createObjectURL(x.response);
        let a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        a.click();
        ele.textContent = raw_textcontent;
    };
    x.onprogress = function (envt) {
        //console.log(envt);
        ele.textContent = raw_textcontent + '(' + Math.round(envt.loaded / 1024 / 1024, 3) + 'M/' + Math.round(envt.total / 1024 / 1024, 3) + 'M '+Math.round(envt.loaded*100/envt.total,3)+'%)';
    };
    x.send();
}

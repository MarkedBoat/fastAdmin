let js = document.createElement('script');
js.src = 'https://markedboat.com/static/tmp1425934/js/hammer/kl-hammer.js';


let chapters = [];
let ifr_loaded_times = 0;
let ifr_deleted_times = 0;
let getCharperContentAction = function (iframes_root1) {
    chapters.forEach(function (chapter, index) {
        if (chapter.status === true) {
            return false;
        }
        if (iframes_root1.childNodes.length > 3) {
            console.log('等待吧', ifr_loaded_times, ifr_deleted_times, chapter);
            return false;
        }
        if (chapter.status === undefined) {
            let ifr = new Emt('iframe', '', '', {src: chapter.url, info: chapter});
            ifr.addEventListener('load', function () {
                console.log(ifr);
                ifr_loaded_times = ifr_loaded_times + 1;
                let content_ele = this.contentDocument.getElementById('chaptercontent');
                if (content_ele === undefined || content_ele===null) {
                    chapters[index].status = true;
                    this.remove();
                    ifr_deleted_times += 1;
                    console.log('移除 有问题的 iframe');
                    return false;
                }
                try {
                    let ps = content_ele.getElementsByTagName('p');
                    for (let j = 0; j < ps.length; j++) {
                        ps[j].remove();
                    }
                    let scripts = content_ele.getElementsByTagName('script');
                    for (let j = 0; j < scripts.length; j++) {
                        scripts[j].remove();
                    }
                    let divs = content_ele.getElementsByTagName('div');
                    for (let j = 0; j < divs.length; j++) {
                        divs[j].remove();
                    }
                    console.log(content_ele, content_ele.innerText);
                    kl.ajax({
                        url: 'https://markedboat.com/novel/spider_chapter',
                        data: {
                            novel_id: chapter.novel_id,
                            part_index: chapter.part_index,
                            chapter_index: chapter.chapter_index,
                            content: content_ele.innerText
                        },
                        type: 'json',
                        success: function (data) {
                            console.log(data);
                            if (kl.isUndefined(data, 'data.chapter')) {
                                alert('数据有问题');
                            } else {
                                chapters[index].status = true;
                                ifr.remove();
                                ifr_deleted_times += 1;
                                console.log('移除iframe');
                            }
                        },
                        error: function () {
                            alert('错误');
                        },
                        isAjax: false
                    });
                } catch (e) {
                    console.log(content_ele, ifr);
                }


                console.log(content_ele, content_ele.innerText);
                //ifr.remove();getCharperContentAction();


            });
            iframes_root1.addNodes([ifr]);

        }
    })
};
let getChapterContent = function () {
    console.log('开始抓内容');
    let iframes_root = new Emt('div');
    document.body.append(iframes_root);
    let j = 0;
    setInterval(function () {
        j++;
        console.log(j);
        getCharperContentAction(iframes_root);
    }, 1000);
};


let getChapters = function () {
    let t = kl.id('chapterlist');
    let as = t.getElementsByTagName('a');
    console.log(as);
    let list = [];
    for (let i = 0; i < as.length; i++) {
        list.push({index: i, url: as[i].href, text: as[i].textContent});
    }
    kl.ajax({
            url: 'https://markedboat.com/novel/spider_chapters',
            data: {
                title: kl.id('top').textContent,
                index_url: document.location.href,
                chapters: JSON.stringify(list)
            },
            type: 'json',
            success: function (data) {
                console.log(data);
                if (kl.isUndefined(data, 'data.chapters')) {
                    alert('数据有问题');
                } else {
                    chapters = [];
                    data.data.chapters.forEach(function (chapter) {
                        chapters.push({
                            chapter_index: chapter.chapter_index,
                            novel_id: chapter.novel_id,
                            part_index: 0,
                            url: chapter.detail_url,
                        });
                        chapters.push({
                            chapter_index: chapter.chapter_index,
                            novel_id: chapter.novel_id,
                            part_index: 1,
                            url: chapter.detail_url.replace('.html', '_2.html'),
                        });
                    });
                    console.log('xxxxxx', chapters);
                    getChapterContent();
                }
            },
            error: function () {

            },
            isAjax: false
        }
    )
};
js.addEventListener('load', function () {
    console.log('Ctrl + X');
    document.onkeydown = function (e) {
        var keyCode = e.key || e.which || e.charCode;
        var altKey = e.ctrlKey;
        if (altKey && keyCode == 'x') {
            //alert("组合键成功")
            console.log('开始');
            getChapters();
            e.preventDefault();
            return false;

        }

    }
});

document.body.append(js);
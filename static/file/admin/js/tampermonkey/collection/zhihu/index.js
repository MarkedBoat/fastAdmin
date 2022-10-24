let is_stop = false;
window.init_times = window.init_times || 0;
window.init_times = window.init_times + 1;
if (window.init_times > 2) {
    console.log('xxxxx');
}

let alertMsg = function (msg, stop) {
    kl.log(msg);
    alert(msg);
    is_stop = stop === true;
};


let postAnswer = function (data) {
    console.log(data);
    if (typeof window.kl === "object" && window.kl.test === true) {
        console.log('调试呢');
    } else {
        kl.ajax({
            url: 'https://markedboat.com/cors/zhihu',
            data: data,
            success: function (res) {
                console.log(res);
                if (res && res.status) {
                    console.log(res.status, res.status === 200, res.data);
                    if (res.status === 200) {
                        alertMsg('成功');
                    } else {
                        alertMsg(res.msg || '错误 请检查数据结构');
                    }

                } else {
                    alertMsg('服务异常');
                }
            },
            isAjax: false,
            type: 'json'
        });
    }

};


let initAnswers = function () {
    if (window.location.href.indexOf('https://www.zhihu.com/question') === -1) {
        console.log('知乎回答开始初始化js 不是QA，停止');
        return false;
    } else {
        console.log('知乎回答开始初始化js goon');

    }
    let title;
    let tagNames = [];
    let Qdetail = '';

    let h1Nodes = kl.xpathSearch(".//h1[@class='QuestionHeader-title']/text()");
    if (h1Nodes.length !== 2) {
        alertMsg('找不到H1 标题', true);
        return false;
    }
    title = h1Nodes[0].textContent;

    let tagNodes = kl.xpathSearch(".//div[@class='QuestionHeader-tags']//div[@class='Popover']");
    if (tagNodes.length < 1) {
        alertMsg('找不到标签', true);
        return false;
    }
    tagNodes.forEach(function (tmpNode) {
        tagNames.push(tmpNode.textContent.trim());
    });

    let tmpDivs = kl.xpathSearch(".//div[@class='ContentItem AnswerItem']");
    if (tmpDivs.length < 1) {
        alertMsg('没有答案div', true);
        return false;
    }

    let tmpUrlNodes = kl.xpathSearch(".//meta[@itemprop='url']", tmpDivs[0]);
    if (tmpUrlNodes.length < 2) {
        alertMsg('在答案div 找不到url', true);
        return false;
    }
    let tmpAuthorNodes = kl.xpathSearch(".//a[@class='UserLink-link']", tmpDivs[0]);
    if (tmpAuthorNodes.length < 2) {
        let tmpAuthorNodes2 = kl.xpathSearch(".//span[@class='UserLink AuthorInfo-name']", tmpDivs[0]);
        if (tmpAuthorNodes2.length !== 1 || tmpAuthorNodes2[0].textContent !== '匿名用户') {
            alertMsg('在答案div 找不到作者信息', true);
            return false;
        }
    }

    try {
        let tmp_obj = JSON.parse(kl.id('js-initialData').textContent);
        console.log('tmp_obj.initialState.entities.questions', tmp_obj.initialState.entities.questions);
        let tmp_cnt = 0;
        let question_info = {};
        for (let id in tmp_obj.initialState.entities.questions) {
            console.log(id);
            question_info = tmp_obj.initialState.entities.questions[id];
            tmp_cnt++;
        }
        if (tmp_cnt !== 1) {
            alertMsg('问题信息解析失败' + e.message, true);
            return false;
        }
        Qdetail = question_info.detail;

    } catch (e) {
        alertMsg('寻找问题信息:' + e.message, true);
        return false;
    }


    let tmpContentNodes = kl.xpathSearch(".//span[contains(@class,'RichText ztext CopyrightRichText-richText')]", tmpDivs[0]);
    if (tmpContentNodes.length < 1) {
        alertMsg('在答案div 找不到内容', true);
        return false;
    }

    let tmpAnchorNodes = kl.xpathSearch(".//div[@class='ContentItem-meta']", tmpDivs[0]);
    if (tmpAnchorNodes.length < 1) {
        alertMsg('在答案div 找不到嵌入位置', true);
        return false;
    }

    let getAnswers = function () {
        let tmpNodes = kl.xpathSearch(".//div[@class='ContentItem AnswerItem']");
        console.log('此次遍历', tmpNodes.length, window.init_times);
        let init_cnt = 0;
        let skip_cnt = 0;
        tmpNodes.forEach(function (tmpNode) {
            if (tmpNode.isInit === undefined) {
                init_cnt++;
                console.log('init:' + init_cnt.toString());
                let anchor = kl.xpathSearch(".//div[@class='ContentItem-meta']", tmpNode)[0];
                let link = kl.xpathSearch(".//meta[@itemprop='url']", tmpNode)[1].getAttribute('content');

                let tmpAuthorNodes3 = kl.xpathSearch('.//div[contains(@class,"AuthorInfo")]//span[contains(@class,"AuthorInfo-name")]', tmpDivs[0]);

                if (tmpAuthorNodes3.length !== 1) {
                    alertMsg('在答案div 找不到作者信息', true);

                }
                let userInfo = tmpAuthorNodes3[0];


                let content = kl.xpathSearch(".//span[contains(@class,'RichText ztext CopyrightRichText-richText')]", tmpNode)[0].innerHTML;

                let btn = new Emt('button', 'type="button"', '收藏回答');
                let btn_test = new Emt('button', 'type="button"', 'test');

                anchor.append(btn);
                anchor.append(btn, btn_test);
                let tmp_info = {
                    author: userInfo.textContent,
                    title: title,
                    question_detail: Qdetail,
                    content: content + "<br>" + userInfo.outerHTML,
                    link: link,
                    tags: tagNames,
                    type: 'answer'
                };
                btn.addEventListener('click', function () {
                    postAnswer(tmp_info);
                });
                btn_test.addEventListener('click', function () {
                    console.log(tmp_info);
                });
                tmpNode.isInit = true;
            } else {
                skip_cnt++;
                console.log('skip:' + skip_cnt.toString());
            }
        });

    };


    setInterval(function () {
        if (is_stop === false) {
            getAnswers();
        }
    }, 1000);


};

let initZhuanlan = function () {
    if (window.location.href.indexOf('https://zhuanlan.zhihu.com/p/') === -1) {
        console.log('知乎专栏开始初始化js 不是专栏，停止');
        return false;
    } else {
        console.log('知乎专栏开始初始化js goon');

    }

    let title;
    let tagNames = [];

    let h1Nodes = kl.xpathSearch('.//h1[@class="Post-Title"]');

    if (h1Nodes.length !== 1) {
        alertMsg('找不到专栏H1 标题');
        return false;
    }
    title = h1Nodes[0].textContent;

    let tagNodes = kl.xpathSearch(".//div[@class='TopicList Post-Topics']//div[@class='Popover']");
    if (tagNodes.length < 1) {
        alertMsg('找不到专栏标签');
        return false;
    }
    tagNodes.forEach(function (tmpNode) {
        tagNames.push(tmpNode.textContent.trim());
    });


    let tmpAuthorNodes = kl.xpathSearch(".//div[@class='AuthorInfo-head']//a[@class='UserLink-link']");
    if (tmpAuthorNodes.length !== 1) {
        alertMsg('在专栏div 找不到作者信息');
        return false;
    }
    let tmpContentNodes = kl.xpathSearch(".//div[contains(@class,'RichText ztext Post-RichText')]");
    if (tmpContentNodes.length !== 1) {
        alertMsg('在专栏div 找不到内容');
        return false;
    }

    let tmpAnchorNodes = kl.xpathSearch(".//div[@class='Post-RichTextContainer']");
    if (tmpAnchorNodes.length !== 1) {
        alertMsg('在专栏div 找不到嵌入位置');
        console.log(tmpAnchorNodes);
        return false;
    }

    let btn = new Emt('button', 'type="button"', '收藏');
    tmpAnchorNodes[0].parentElement.insertBefore(btn, tmpAnchorNodes[0]);

    let btn_test = new Emt('button', 'type="button"', 'test');
    tmpAnchorNodes[0].parentElement.insertBefore(btn_test, tmpAnchorNodes[0]);

    let zhuan_data = {
        author: tmpAuthorNodes[0].textContent,
        title: title,
        content: tmpContentNodes[0].innerHTML + "<br>" + tmpAuthorNodes[0].outerHTML,
        link: window.location.href,
        tags: tagNames,
        type: 'answer'
    };

    btn.addEventListener('click', function () {
        postAnswer(zhuan_data);
    });
    btn_test.addEventListener('click', function () {
        console.log(zhuan_data);
    });
};
let initVideo = function () {
    if (window.location.href.indexOf('https://www.zhihu.com/zvideo/') === -1) {
        console.log('知乎video开始初始化js 不是专栏，停止');
        return false;
    } else {
        console.log('知乎video开始初始化js goon');

    }

    let title;
    let tagNames = [];

    let h1Nodes = kl.xpathSearch('.//h1[@class="ZVideo-title"]');

    if (h1Nodes.length !== 1) {
        alertMsg('找不到video H1 标题');
        return false;
    }
    title = h1Nodes[0].textContent;

    let tagNodes = kl.xpathSearch(".//div[@class='ZVideo-tagsContainer']//a[@class='ZVideoTag']");
    if (tagNodes.length < 1) {
        alertMsg('找不到video标签');
        return false;
    }
    tagNodes.forEach(function (tmpNode) {
        tagNames.push(tmpNode.textContent.trim());
    });


    let tmpContentNodes = kl.xpathSearch('.//p[@class="ZVideo-description"]');

    if (tmpContentNodes.length !== 1) {
        alertMsg('在 video div 找不到视频描述');
        return false;
    }

    let tmpAuthorNodes = kl.xpathSearch('.//span[@class="UserLink ZVideo-authorName"]//a[@class="UserLink-link"]');
    if (tmpAuthorNodes.length !== 1) {
        alertMsg('在 video div 找不到作者信息');
        return false;
    }

    let tmpAnchorNodes = kl.xpathSearch('.//div[@class="ZVideo-meta"]');
    if (tmpAnchorNodes.length !== 1) {
        alertMsg('在video div 找不到嵌入位置');
        console.log(tmpAnchorNodes);
        return false;
    }

    let btn = new Emt('button', 'type="button"', '收藏');
    tmpAnchorNodes[0].parentElement.insertBefore(btn, tmpAnchorNodes[0]);

    let btn_test = new Emt('button', 'type="button"', 'test');
    tmpAnchorNodes[0].parentElement.insertBefore(btn_test, tmpAnchorNodes[0]);

    let zhuan_data = {
        author: tmpAuthorNodes[0].textContent,
        title: title,
        content: tmpContentNodes[0].textContent,
        link: window.location.href,
        tags: tagNames,
        type: 'video'
    };

    btn.addEventListener('click', function () {
        postAnswer(zhuan_data);
    });
    btn_test.addEventListener('click', function () {
        console.log(zhuan_data);
    });
};


initAnswers();
initZhuanlan();
initVideo();





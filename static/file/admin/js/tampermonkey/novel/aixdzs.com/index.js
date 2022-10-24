console.log('注入脚本 检查 typeof KL', typeof KL);
let msgAlert = function (msg) {
    alert(msg);
    console.log(msg);
    return false;
};

function trySpider() {
    console.log('爱下电子书 begain');

    let tmp_h1s = kl.xpathSearch('.//h1');
    if (tmp_h1s.length !== 1) {
        return msgAlert('找不到标题');
    }
    let tmp_flag = kl.xpathSearch('.//span[contains(text(),"作者：")]');
    if (tmp_h1s.length !== 1) {
        return msgAlert('找不到作者信息 前缀');
    }
    if (tmp_flag[0].nextElementSibling === undefined) {
        return msgAlert('找不到作者信息A');
    }

    let title = tmp_h1s[0].textContent + '-' + tmp_flag[0].nextElementSibling.textContent;
    console.log('小说标题:' + title);
    let tmp_ifr = new Emt('iframe');
    document.body.append(tmp_ifr);
    tmp_ifr.links = [];
    tmp_ifr.links_indexs = [];
    tmp_ifr.links_index = 0;
    tmp_ifr.spidering_a = '';
    tmp_ifr.is_spidering = false;

    tmp_ifr.gotoNextSrc = function () {
        if (tmp_ifr.links[tmp_ifr.links_index] === undefined) {
            return msgAlert('已经spider完了');
        }
        tmp_ifr.links_indexs.push(tmp_ifr.links_index);
        tmp_ifr.spidering_a = {
            src: tmp_ifr.links[tmp_ifr.links_index].href,
            title: tmp_ifr.links[tmp_ifr.links_index].textContent,
            index: tmp_ifr.links_index
        };
        console.log('gotoNextSrc', tmp_ifr.spidering_a);
        tmp_ifr.src = tmp_ifr.spidering_a.src;
        tmp_ifr.links_index = tmp_ifr.links_index + 1;

    };
    tmp_ifr.tryGetContent = function () {
        console.log('tryGetContent', tmp_ifr.spidering_a, tmp_ifr.links.length);
        let tmp_divs = kl.xpathSearch('.//div[@class="content"]', tmp_ifr.contentDocument);
        if (tmp_divs.length !== 1) {
            console.log('找不到内容信息', tmp_ifr.spidering_a);
            return false;
        }
        let tmp_ps = kl.xpathSearch('.//p', tmp_divs[0]);
        tmp_ps.forEach(function (tmp_p, tmp_index) {
            tmp_ps[tmp_index].innerHTML = tmp_ps[tmp_index].innerHTML + "\n";
        });
        kl.ajax({
            url: 'https://markedboat.com/novel/spider_chapter_to_file',
            data: {
                title: title,
                page: tmp_ifr.spidering_a.src,
                content: tmp_divs[0].textContent,
                index: tmp_ifr.spidering_a.index + 1,
                chapter: tmp_ifr.spidering_a.title,
            },
            type: 'json',
            success: function (data) {
                console.log(data);
                if (kl.isUndefined(data, 'data.page')) {
                    msgAlert('数据有问题');
                } else {

                }
                tmp_ifr.gotoNextSrc();
            },
            error: function (msg) {
                tmp_ifr.gotoNextSrc();
                return msgAlert(msg);

            },
            isAjax: false
        });
    };
    tmp_ifr.addEventListener('load', function () {
        console.log('loaded', tmp_ifr.spidering_a);
        try {
            tmp_ifr.tryGetContent();
        } catch (e) {
            console.log(e);
        }
    });


    let spider = function () {
        let tmp_as = kl.xpathSearch('.//div[@id="i-chapter"]//li[@class="chapter"]//a');
        // msgAlert(tmp_as);
        let tmp_len = tmp_as.length;
        tmp_ifr.links = tmp_as.slice(0, 3);
        tmp_ifr.links = tmp_as;
        tmp_ifr.gotoNextSrc();
    };

    document.onkeydown = function (e) {
        var keyCode = e.key || e.which || e.charCode;
        var altKey = e.ctrlKey;
        if (altKey && keyCode == 'x') {
            //alert("组合键成功")
            console.log('下载');
            spider();
            e.preventDefault();
            return false;

        }

    }
}

trySpider();



function sehuatang() {
    console.log('sehuatang begain');
    var style = new Emt('style');
    style.innerHTML = '' +
        '.d_list{width:100%}' +
        '.d_img{height:500px;width:auto;border:3px solid #AAA;}' +
        '.d_ele{padding:30px;border:5px solid #000}' +
        '.d_img_box{margin:70px;padding:30px;}' +
        '.hide{display:none;}' +
        '';


    var ifr = new Emt('iframe').setPros({id: 'ifr'});
    var div_list = new Emt('div').setPros({className: 'd_list'});
    document.body.appendChild(style);
    document.body.appendChild(div_list);
    document.body.appendChild(ifr);

    var t = document.getElementById('threadlisttableid');
    if (t === null) {
        return false;
    }
    var ths = t.getElementsByClassName('common');
    var ths_len = ths.length;
    var as = [];
    for (var i = 0; i < ths_len; i++) {
        var th = ths[i];
        var tmp_as = th.getElementsByTagName('a');
        if (tmp_as.length < 3) continue;
        if (tmp_as[2].previousElementSibling.tagName !== 'EM') continue;
        console.log(tmp_as, tmp_as[2]);
        as.push({src: tmp_as[2].href, t: tmp_as[2].textContent});

    }
    console.log(as);
    var url_index = 0;

    function gethtml(as, i) {
        var div_show = new Emt('div').setPros({className: 'd_ele'});
        var ifr_tmp = new Emt('iframe').setPros({id: 'ifr', div_show: div_show, className: 'hide'});
        div_show.ifr = ifr_tmp;
        console.log('gethtml', i, as[i]);
        if (typeof as[i] === 'undefined') {
            console.log('结束iframe');
            return false;
        }
        div_show.addNodes([
                new Emt('h1').setPros({textContent: as[i].t}),
                new Emt('a').setPros({
                    textContent: i.toString() + '/' + as.length + ' 打开',
                    href: as[i].src,
                    target: '_blank'
                }),
                ifr_tmp
            ]
        ).setPros({info: as[i]});
        div_list.addNode(div_show);

        ifr_tmp.addEventListener('load', function () {

            console.log('ifr ok');

            let img_srcnodes = kl.xpathSearch('.//img//@file', kl.xpathSearch('.//div[contains(@id,"post_")]//table[contains(@id,"pid")]', this.contentDocument)[0]);

            img_srcnodes.forEach(function (img_srcnode) {
                let tmp_img = new Emt('img').setPros({
                    src: img_srcnode.nodeValue,
                    className: 'd_img'
                });
                ifr_tmp.div_show.addNodes([
                    new Emt('div').setPros({className: 'd_img_box'}).addNodes([tmp_img])
                ]);
                tmp_img.addEventListener('click', function () {
                    this.classList.toggle('d_img');
                })
            });
            var dlinks = this.contentDocument.getElementsByClassName('attnm');
            if (dlinks.length) {
                this.div_show.addNodes([new Emt('h1').setPros({textContent: this.div_show.info.t}), dlinks[0]]);
            }

            url_index = url_index + 1;
            gethtml(as, url_index);

        });

        if (i + 1 > as.length) {
            alert('ok');
            return false;
        } else {
            div_show.ifr.src = div_show.info.src;
        }

    }


    document.onkeydown = function (e) {
        var keyCode = e.key || e.which || e.charCode;
        var altKey = e.ctrlKey;
        if (altKey && keyCode == 'x') {
            //alert("组合键成功")
            console.log('下载');
            gethtml(as, url_index);
            e.preventDefault();
            return false;

        }

    }
}

sehuatang();



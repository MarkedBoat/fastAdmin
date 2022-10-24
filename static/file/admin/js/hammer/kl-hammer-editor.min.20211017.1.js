let initEditor = function (opt_input) {


    if (!opt_input.data_ele) {
        throw '必须设置data_ele,作为html字符串存放目标';
    }

    if (!opt_input.ui_ele) {
        opt_input.ui_ele = new Emt('div');
        opt_input.data_ele.parentElement.insertBefore(opt_input.ui_ele, opt_input.data_ele);
        opt_input.data_ele.classList.add('hide');
        kl.log('自动生成editor.ui_ele');
    }


    opt_input.funs = opt_input.funs || {};
    if (!opt_input.btns || typeof opt_input.btns.forEach !== 'function') {
        throw '必须设置btns';
    }
    let raw_root = opt_input.ui_ele;
    raw_root.setAttribute('style', 'width:100%;height:auto;min-height:300px;font-size:1em');
    let text_area = new Emt('div').setPros({
        className: 'text_area',
        contentEditable: true
    }).setAttrs({'style': 'width:100%;height:auto;min-height:300px;padding-left:2px;border:1px solid #000;'});
    let btns_area = new Emt('div').setPros({className: 'btns_area'});
    raw_root.append(
        btns_area,
        text_area
    );
    //text_area.innerHTML = opt_input.data_ele.value;

    let btns = [];

    raw_root.afterChange = function () {
        kl.log(text_area.innerHTML, 'change');
    };
    text_area.addEventListener('input', function () {
        //kl.log(text_area.innerHTML, 'input');
        opt_input.data_ele.value = text_area.innerHTML;
        //opt_input.data_ele.value = text_area.innerText;

    });

    let userSelection;
    if (window.getSelection) { //现代浏览器
        userSelection = window.getSelection();
    } else if (document.selection) { //IE浏览器
        userSelection = document.selection.createRange();
    }

    function execCommand(cmd, bool, value) {
        text_area.focus();
        return document.execCommand(cmd, bool, value);
    }

    function setTagName(tagName) {
        document.execCommand('FormatBlock', false, tagName);
    }

    if (opt_input.btns.indexOf('h1') !== -1) {
        let opt_h1 = new Emt('button').setPros({className: 'opt_btn opt_btn_h1', textContent: 'h1'});
        opt_h1.addEventListener('click', function (e) {
            setTagName('h1');
            e.preventDefault();
        });
        btns.push(opt_h1);
    }


    if (opt_input.btns.indexOf('bold') !== -1) {
        let opt_bold = new Emt('button').setPros({className: 'opt_btn opt_btn_bold', textContent: '加粗'});
        opt_bold.addEventListener('click', function (e) {
            execCommand('bold', false, null);
            e.preventDefault();
        });
        btns.push(opt_bold);
    }

    if (opt_input.btns.indexOf('video') !== -1) {
        if (typeof opt_input.funs.video !== 'function') {
            throw 'funs.video 未设置,需要设置一个回调 raw_root.addVideoBloc 的方法';
        }
        raw_root.addVideoBlock = function (video_src, video_title) {
            kl.log('插入视频', video_title, video_src);
            execCommand(
                'insertHTML',
                true,
                "\n" + '<kleditorfile contenteditable="false" type="video" src="' + video_src + '"><file_type>[视频]:</file_type>' + video_title + '</kleditorfile>' + "\n"
            );

            //execCommand('insertHTML', false, (new Emt('p').setPros({className: 'text-center', textContent: '#'})).outerHTML);
        };
        let opt_video = new Emt('button').setPros({className: 'opt_btn opt_btn_video', textContent: '视频'});
        opt_video.addEventListener('click', function (e) {
            //execCommand('bold', false, null);
            opt_input.funs.video(raw_root.addVideoBlock);
            e.preventDefault();
        });
        btns.push(opt_video);
    }
    if (opt_input.btns.indexOf('img') !== -1) {
        let opt_img = new Emt('button').setPros({className: 'opt_btn opt_btn_img', textContent: '图片'});
        if (typeof opt_input.funs.img !== 'function') {
            throw 'funs.img 未设置,需要设置一个回调 raw_root.addImglock 的方法';
        }
        raw_root.addImgBlock = function (img_src) {
            kl.log('插入图片', img_src);
            let p_img = new Emt('p').setPros({className: 'text-center'}).setAttrsByStr('contenteditable="false"').addNodes([
                new Emt('img').setPros({className: 'img img-thumbnail img-responsive', src: img_src}).setAttrs({style: 'max-width:100%;'}),
                new Emt('span').setPros({textContent: '#'})
            ]);
            execCommand('insertHTML', false, p_img.outerHTML);
            //execCommand('insertHTML', false, (new Emt('p').setPros({className: 'text-center', textContent: '#'})).outerHTML);
        };

        opt_img.addEventListener('click', function (e) {
            opt_input.funs.img(raw_root.addImgBlock);
            e.preventDefault();
        });
        btns.push(opt_img);
    }
    if (opt_input.btns.indexOf('file') !== -1) {
        let opt_file = new Emt('button').setPros({className: 'opt_btn opt_btn_file', textContent: '文件'});
        if (typeof opt_input.funs.file !== 'function') {
            throw 'funs.file 未设置,需要设置一个回调 raw_root.addFileBolock 的方法';
        }
        raw_root.addFileBolock = function (file_name, file_link, file_sets) {
            kl.log('插入文件', file_name, file_link);
            execCommand(
                'insertHTML',
                true,
                "\n" + '<kleditorfile contenteditable="false" type="' + (file_sets || 'file') + '" src="' + file_link + '"><file_type>[文件]:</file_type>' + file_name + '</kleditorfile>' + "\n"
            );
        };

        opt_file.addEventListener('click', function (e) {
            opt_input.funs.file(raw_root.addFileBolock);
            e.preventDefault();
        });
        btns.push(opt_file);
    }

    if (opt_input.btns.indexOf('link') !== -1) {
        let opt_link = new Emt('button').setPros({className: 'opt_btn opt_btn_link', textContent: '链接'});
        if (typeof opt_input.funs.link !== 'function') {
            throw 'funs.link 未设置,需要设置一个回调 raw_root.addLinkBlock 的方法';
        }
        raw_root.addLinkBlock = function (link_name, link_url) {
            execCommand(
                'insertHTML',
                false,
                (
                    new Emt('a').setPros({className: 'text-center'}).addNodes([
                        new Emt('a').setPros({className: 'link', href: link_url, textContent: link_name})
                    ])
                ).outerHTML
            );
        };
        opt_link.addEventListener('click', function (e) {
            opt_input.funs.link(raw_root.addLinkBlock);
            e.preventDefault();
        });
        btns.push(opt_link);
    }

    let opt_table = new Emt('button').setPros({className: 'opt_btn opt_btn_table', textContent: '表格'});
    opt_table.addEventListener('click', function (e) {
        execCommand('enableInlineTableEditing', false, null);
        kl.log(text_area.innerHTML, text_area.value);
        e.preventDefault();
    });


    if (opt_input.btns.indexOf('del') !== -1) {
        let opt_del = new Emt('button').setPros({className: 'opt_btn opt_btn_del', textContent: '删除'});
        opt_del.addEventListener('click', function (e) {
            // execCommand('forwardDelete', false, null);
            execCommand('delete', false, null);
            e.preventDefault();
        });
        btns.push(opt_del);
    }


    if (opt_input.btns.indexOf('undo') !== -1) {
        let opt_undo = new Emt('button').setPros({className: 'opt_btn opt_btn_undo', textContent: '撤销'});
        opt_undo.addEventListener('click', function (e) {
            execCommand('undo', false, null);
            e.preventDefault();
        });
        btns.push(opt_undo);
    }

    if (opt_input.btns.indexOf('redo') !== -1) {
        let opt_redo = new Emt('button').setPros({className: 'opt_btn opt_btn_redo', textContent: '取消撤销'});
        opt_redo.addEventListener('click', function (e) {
            execCommand('redo', false, null);
            e.preventDefault();
        });
        btns.push(opt_redo);
    }

    btns_area.addNodes(btns);
    raw_root.getValue = function () {
        return text_area.innerHTML;
    };
    opt_input.data_ele.reloadData = function (html_text) {
        kl.log('指定ui_ele内容', html_text, opt_input.data_ele.value);
        //text_area.innerHTML = opt_input.data_ele.value;
        execCommand('insertHTML', true, html_text || opt_input.data_ele.value);
    };
    opt_input.ui_ele.addEventListener('click', function () {
        kl.log(text_area.innerHTML.length, text_area.innerHTML);
        if (text_area.innerHTML.length === 0) {
            opt_input.data_ele.reloadData();
        }
    });

    kl.log(opt_input.data_ele.value.length);
    if (opt_input.data_ele.value.length) {
        let si = window.setInterval(function () {
            kl.log(opt_input.ui_ele.innerHTML.length);
            if (opt_input.ui_ele.innerHTML.length === 0) {
                opt_input.data_ele.reloadData();
            } else {
                window.clearInterval(si);
            }
        }, 200);
    }
    return {
        reloadData: function (html_text) {
            opt_input.data_ele.reloadData(html_text);
        }
    };

};

function lookLargeImage() {
    let pre_img = new Emt('img').setPros({className: 'carousel-inner  img-rounded'});
    let div_modal = new Emt('div').setAttrsByStr('class="fade modal" role="dialog" tabindex="-1"').setPros({id: 'modal_look_large_image'});
    let handle = {
        root_ele: div_modal,
        show: function () {
            $('#modal_look_large_image').modal('show');
        },
        hide: function () {
            $('#modal_look_large_image').modal('hide');
        },
        loadImgSrc: function (src) {
            pre_img.src = src;
            $('#modal_look_large_image').modal('show');
        }
    };
    pre_img.addEventListener('click', function () {
        $('#modal_look_large_image').modal('hide');
    })
    div_modal.addNodes([
        new Emt('div').setAttrsByStr('class="modal-header"').addNodes([
            new Emt('button').setPros({textContent: 'x'}).setAttrsByStr('type="button" class="close" data-dismiss="modal" aria-hidden="true"'),
        ]),
        //主体
        new Emt('div').setAttrsByStr('class="modal-body"').addNodes([pre_img])
    ])
    /*
    div_modal.addNodes([
        new Emt('div').setAttrsByStr('class="modal-dialog"').addNodes([
                //头部
                new Emt('div').setAttrsByStr('class="modal-header"').addNodes([
                    new Emt('button').setPros({textContent: 'x'}).setAttrsByStr('type="button" class="close" data-dismiss="modal" aria-hidden="true"'),
                ]),
                //主体
                new Emt('div').setAttrsByStr('class="modal-body"').addNodes([

                ])

        ])
    ]);*/

    document.body.append(new Emt('div').addNodes([div_modal]));
    return handle;
}

let hanndle_lookLargeImage = lookLargeImage();

function formatEditorText2Element(root_ele, ext_opts_input) {
    let ext_opts = ext_opts_input || {isExistVideoPlayer: true};
    let files = root_ele.getElementsByTagName('kleditorfile');
    for (let i = 0; i < files.length; i++) {
        let file_ele = files[i];
        let file_type = file_ele.getAttribute('type');
        let file_src = file_ele.getAttribute('src');
        let file_name = file_ele.textContent;
        if (file_type === 'file') {
            file_ele.parentNode.insertBefore(
                new Emt('p').setPros({className: 'text-center '}).addNodes([
                    new Emt('a').setPros({textContent: file_name, href: file_src, target: '_blank'})
                ]),
                file_ele
            );
        } else if (file_type === 'audio') {
            file_ele.parentNode.insertBefore(
                new Emt('p').setPros({className: 'text-center'}).setAttrsByStr('style="border-top: 1px solid #DDD;"').addNodes([
                    new Emt('audio').setPros({
                        className: 'img img-responsive img-thumbnail ',
                        src: file_src,
                        controls: 'controls'
                    }).setAttrsByStr('style="min-width:90%;padding:0px;border:none;min-height:3em;"'),
                    new Emt('p').setPros({className: 'text-center', textContent: file_name,}).setAttrsByStr('style="border-top: 1px solid #DDD;"')
                ]),
                file_ele
            );
        } else if (file_type === 'video') {
            if (document.location.protocol === 'https:') {
                kl.log('穷啊,编辑器检查有视频存在，不支持https访问,将自动跳转以http访问');
                if (ext_opts.isExistVideoPlayer === true) {
                    window.location.href = window.location.href.replace('https:', 'http:');
                    return false;
                }

            }

            file_ele.parentNode.insertBefore(
                new Emt('p').setPros({className: 'text-center'}).setAttrsByStr('style="border-top: 1px solid #DDD;"').addNodes([
                    new Emt('video').setPros({
                        className: 'img img-responsive img-thumbnail ',
                        src: file_src,
                        controls: 'controls'
                    }).setAttrsByStr('style="min-width:90%;padding:0px;border:none;background:#000"'),
                    new Emt('p').setPros({className: 'text-center', textContent: file_name,}).setAttrsByStr('style="border-top: 1px solid #DDD;"')
                ]),
                file_ele
            );
        }
        file_ele.classList.add('hide');
    }

    let imgs = root_ele.getElementsByTagName('img');

    kl.log(imgs);
    let file_ids = [];
    let src_fileId_map = {};
    for (let i = 0; i < imgs.length; i++) {
        let tmp_src = imgs[i];
        let file_id = imgs[i].src.replace(/.*\/upload\/src\?file_id=(\d+)&.*/, '$1');
        src_fileId_map[imgs[i].src] = file_id;
        file_ids.push(file_id);
    }
    let lookLargeImgs = function () {
        for (let i = 0; i < imgs.length; i++) {
            //kl.log(imgs[i], imgs[i].className.indexOf('img-sthumbnail') !== -1);
            if (imgs[i].className.indexOf('img-sthumbnail') !== -1 > -1) {
                imgs[i].parentElement.setAttribute('style', 'border-top: 1px solid #DDD;border-bottom: 1px solid #DDD;');
                imgs[i].addEventListener('click', function () {
                    hanndle_lookLargeImage.loadImgSrc(this.src);
                })
            }
        }
    };
    if (file_ids.length > 0) {
        kl.ajax({
            url: '/imark/file_srcs?file_ids=' + file_ids.join(','),
            method: 'GET',
            type: 'json',
            success: function (srcs_res) {
                console.log(srcs_res, srcs_res.srcs, kl.isUndefined(srcs_res, 'srcs'));
                if (kl.isUndefined(srcs_res, 'srcs') === false) {
                    kl.log('重写src');
                    let old_imgs = [];
                    for (let i = 0; i < imgs.length; i++) {
                        if (typeof src_fileId_map[imgs[i].src] !== "undefined" && srcs_res.srcs[src_fileId_map[imgs[i].src]]) {
                            let new_src = srcs_res.srcs[src_fileId_map[imgs[i].src]];
                            if (imgs[i].className.indexOf('img-sthumbnail') !== -1 > -1) {
                                let new_img = new Emt('img', '', '', {src: new_src, className: imgs[i].className});
                                console.log(new_img);
                                imgs[i].parentElement.setAttribute('style', 'border-top: 1px solid #DDD;border-bottom: 1px solid #DDD;');
                                imgs[i].parentElement.append(new_img);
                                old_imgs.push(imgs[i]);
                                new_img.addEventListener('click', function () {
                                    hanndle_lookLargeImage.loadImgSrc(this.src);
                                })
                            }
                        }
                    }
                    let tmp_len = old_imgs.length;
                    for (let z = 0; z < tmp_len; z++) {
                        old_imgs[z].remove();
                    }
                } else {
                    kl.log('重写src2');
                    lookLargeImgs();
                }

            },
            error: function () {
                kl.log('重写src3');
                lookLargeImgs();
            }
        })
    }
}


function eg() {
    initEditor({
        ui_ele: kl.id('box'),
        data_ele: kl.id('input'),
        btns: ('h1,bold,video,img,file,link,del,undo,redo').split(','),
        funs: {
            video: function (editor_insertVideo_callback) {
                let video_src = 'http://www.markedboat.com/video/src?video_id=1&go=302';//必须实现  插入的视频 src
                editor_insertVideo_callback(video_src, video_title);//必须实现 确定了src 之后,回调 editor,让editor插入视频
            },
            img: function (editor_insertImg_callback) {
                let img_src = 'ddd';//必须实现  插入的视频 src
                img_src = 'https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fupload-images.jianshu.io%2Fupload_images%2F3944667-b4a9e42b46a14946.png&refer=http%3A%2F%2Fupload-images.jianshu.io&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=jpeg?sec=1636867340&t=67e8f8c65afb6d231ef8afa1334035e4';
                editor_insertImg_callback(img_src);//必须实现 确定了src 之后,回调 editor,让editor插入图片
            },
            file: function (editor_insertFile_callback) {
                let url = 'ddd';//必须实现  插入的视频 src
                editor_insertFile_callback('file name', 'http://www.baidu.com');//必须实现 确定了src 之后,回调 editor,让editor插入文件
            },
            link: function (editor_insertLink_callback) {
                let url = 'ddd';//必须实现  插入的视频 src
                editor_insertLink_callback('link name', 'http://www.baidu.com');//必须实现 确定了src 之后,回调 editor,让editor插入链接
            }
        }
    });
}




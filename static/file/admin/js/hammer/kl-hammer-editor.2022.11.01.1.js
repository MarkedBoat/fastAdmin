let hammerEditor = function (opt_input) {

    let apiHandle = {};

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
    let text_area = new Emt('div').setPros({className: 'text_area', contentEditable: true}).setAttrs({'style': 'width:100%;height:auto;min-height:300px;padding-left:2px;border:1px solid #000;'});
    let btns_area = new Emt('div').setPros({className: 'btns_area'});
    let sync_div = new Emt('div').setPros({className: 'sync_div'});
    raw_root.append(
        btns_area,
        text_area,
        sync_div
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


    if (opt_input.btns.indexOf('header') !== -1) {
        let opt_header = new Emt('select').setPros({className: 'opt_btn opt_btn_header', textContent: 'H'});
        opt_header.add(new Option('h1', 'h1'));
        opt_header.add(new Option('h2', 'h2'));
        opt_header.add(new Option('h3', 'h3'));
        opt_header.add(new Option('h4', 'h4'));
        opt_header.add(new Option('h5', 'h5'));
        opt_header.add(new Option('h6', 'h6'));


        opt_header.addEventListener('change', function (e) {
            setTagName(opt_header.value);
            e.preventDefault();
        });
        btns.push(opt_header);
    }


    if (opt_input.btns.indexOf('bold') !== -1) {
        let opt_bold = new Emt('button').setPros({className: 'opt_btn opt_btn_bold', textContent: '加粗'});
        opt_bold.addEventListener('click', function (e) {
            execCommand('bold', false, null);
            e.preventDefault();
        });
        btns.push(opt_bold);
    }

    if (opt_input.btns.indexOf('fontsize') !== -1) {
        let opt_fontsize = new Emt('select').setPros({className: 'opt_btn opt_btn_fontsize', textContent: 'size'});
        opt_fontsize.add(new Option('1/10px', '1'));
        opt_fontsize.add(new Option('2/12px', '2'));
        opt_fontsize.add(new Option('3/16px', '3'));
        opt_fontsize.add(new Option('4/18px', '4'));
        opt_fontsize.add(new Option('5/24px', '5'));
        opt_fontsize.add(new Option('6/32px', '6'));
        opt_fontsize.add(new Option('7/48px', '7'));

        opt_fontsize.addEventListener('change', function (e) {
            document.execCommand('fontsize', false, opt_fontsize.value);
            e.preventDefault();
        });
        btns.push(opt_fontsize);
    }

    apiHandle.flushDataToTextarea = function () {
        opt_input.data_ele.value = text_area.innerHTML;
    };
    apiHandle.addFilePlaceHolder = function (file_name, file_link, file_type) {
        kl.log('插入文件', file_name, file_link, file_type);
        if (file_type === undefined || (['image', 'video', 'file']).indexOf(file_type) === -1) {
            throw '不能接受的文件类型';
        }
        let pre_view_div = new Emt('p');

        let file_name_p = new Emt('p', '', file_name);
        let msg_p = new Emt('p', 'contenteditable="false"', 'wait');
        let op_p = new Emt('p', 'contenteditable="false"', '#');


        let file_place_holder = new Emt('filePlaceHorder', 'contenteditable="false" type="' + file_type + '" src="' + file_link + '"').addNodes([pre_view_div, file_name_p]);
        file_name_p.setAttribute('style', 'display:block;border:1px solid #000;');
        let select_self_btn = new Emt('button', 'type="button"', '选中');
        op_p.addNode(select_self_btn);
        select_self_btn.addEventListener('click', function () {
            let selection = window.getSelection();
            let range = document.createRange();
            range.selectNodeContents(file_place_holder);
            selection.removeAllRanges();
            selection.addRange(range);
        });
        file_place_holder.setFilePlaceHolderType = function (typeName) {
            file_place_holder.setAttribute("type", typeName);
            apiHandle.flushDataToTextarea();
        };
        file_place_holder.setFilePlaceHolderSrc = function (src) {
            file_place_holder.setAttribute("src", src);
            console.log('setFilePlaceHolderSrc', src);
            if (file_type === 'image') {
                text_area.insertBefore(new Emt('img', 'class="editor_img"', '', {src: src}), file_place_holder);
                file_place_holder.remove();
                msg_p.remove();
                op_p.remove();
            } else if (file_type === 'video') {
                text_area.insertBefore(
                    new Emt('div', 'class="video_placeholder"').addNodes([
                        new Emt('video', 'class="editor_video"', '', {src: src}),
                        new Emt('p', '', file_name)
                    ]),
                    file_place_holder);
                file_place_holder.remove();
                msg_p.remove();
                op_p.remove();
            } else {
                text_area.insertBefore(new Emt('a', 'class="editor_file"', file_name, {href: src}), file_place_holder);
                file_place_holder.remove();
                msg_p.remove();
                op_p.remove();
            }
            apiHandle.flushDataToTextarea();

        };

        file_place_holder.setFilePlaceHolderMsg = function (msg_text) {
            msg_p.textContent = msg_text;
        };

        file_place_holder.setFilePlaceHolderFilenameText = function (filename_str) {
            file_name_p.textContent = filename_str;
        };

        file_place_holder.setFilePlaceHolderPreviewElement = function (ele) {
            pre_view_div.innerHTML = '';
            pre_view_div.addNode(ele);
        };
        file_place_holder.setSourceFile = function (file) {
            file_place_holder.sourceFile = file;
            return file_place_holder;
        };
        file_place_holder.getSourceFile = function () {
            return file_place_holder.sourceFile;
        };

        text_area.focus();
        text_area.append(new Emt('div', '', '#'));
        text_area.append(file_place_holder);
        text_area.append(msg_p);
        text_area.append(op_p);
        text_area.append(new Emt('div', '', '#'));

        return file_place_holder;
    };

    if (opt_input.btns.indexOf('file') !== -1) {
        let opt_file = new Emt('button', 'type="button"').setPros({className: 'opt_btn opt_btn_file', textContent: '文件'});
        if (typeof opt_input.funs.uploadFile !== 'function') {
            throw 'funs.uploadFile 未设置,初始化参数需要提供 funs.uploadFile 的方法';
        }
        opt_file.addEventListener('click', function (e) {
            //调起 业务方法 funs.uploadFile，业务方法 (funs.uploadFile) 提供 上传UI（选择、进度、完成），在完成之后改写 编辑器内容
            //1.此文件是简单化处理，只提供最基础的操作（复杂业务需要UI），所以上传交给业务，未集成上传功能，故而 业务需要调用 apiHandle.addFilePlaceHolder ，并且后续跟踪处理
            //2.
            opt_input.funs.uploadFile(apiHandle);
            e.preventDefault();
        });
        btns.push(opt_file);
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

    opt_input.ui_ele.addEventListener('paste', function (event) {
        console.log('onpaste');
        let len = event.clipboardData.items.length;
        for (let i = 0; i < len; i++) {
            console.log(event.clipboardData.items[i], event.clipboardData.items[i].getAsFile(), 'xxx');
            if (event.clipboardData.items[i].type.match(/^image\//)) {
                event.preventDefault();
                let file_obj = event.clipboardData.items[i].getAsFile();
                console.log('file_obj', file_obj);

                if (typeof opt_input.funs.uploadPasteFile !== 'function') {
                    throw 'funs.uploadFile 未设置,初始化参数需要提供 funs.uploadFile 的方法';
                }
                //调起 业务方法 funs.uploadPasteFile，业务方法 (funs.uploadFile) 提供 上传UI（选择、进度、完成），在完成之后改写 编辑器内容
                //1.此文件是简单化处理，只提供最基础的操作（复杂业务需要UI），所以上传交给业务，未集成上传功能，故而 业务需要调用 apiHandle.addFilePlaceHolder ，并且后续跟踪处理
                //2.
                let view_ele = new Emt('img');
                let fr_preview = new FileReader();
                fr_preview.file = file_obj;
                fr_preview.onload = function (evt) {
                    view_ele.src = evt.target.result;
                };
                fr_preview.readAsDataURL(file_obj);
                let filePlacehoder = apiHandle.addFilePlaceHolder(file_obj.name, '', 'image');
                filePlacehoder.setFilePlaceHolderPreviewElement(view_ele);
                filePlacehoder.setSourceFile(file_obj);
                opt_input.funs.uploadPasteFile(apiHandle, filePlacehoder);

            }
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
    apiHandle.appendSyncElement = function (ele) {
        sync_div.addNodes([
            ele,
            new Emt('p')
        ]);
    };
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
    let files = root_ele.getElementsByTagName('filePlaceHorder');
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
                    new Emt('audio').setPros({className: 'img img-responsive img-thumbnail ', src: file_src, controls: 'controls'}).setAttrsByStr('style="min-width:90%;padding:0px;border:none;min-height:3em;"'),
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
                    new Emt('video').setPros({className: 'img img-responsive img-thumbnail ', src: file_src, controls: 'controls'}).setAttrsByStr('style="min-width:90%;padding:0px;border:none;background:#000"'),
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
        src_fileId_map[imgs[i]] = file_id;
        file_ids.push(file_id);
    }
    kl.ajax({
        url: '/upload/srcs?file_ids=' + file_ids.join(','),
        method: 'GET',
        type: 'json',
        success: function (data) {
            for (let i = 0; i < imgs.length; i++) {
                kl.log(imgs[i], imgs[i].className.indexOf('img-sthumbnail') !== -1);
                if (imgs[i].className.indexOf('img-sthumbnail') !== -1 > -1) {
                    imgs[i].parentElement.setAttribute('style', 'border-top: 1px solid #DDD;border-bottom: 1px solid #DDD;');
                    imgs[i].addEventListener('click', function () {
                        hanndle_lookLargeImage.loadImgSrc(this.src);
                    })
                }
            }
        }
    })


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




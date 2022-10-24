/**
 * 需要  upload 和 editor 支持
 */
let hammerMixedEditor = function (input_options) {
    let input_opt = input_options || {};
    let video_uploader = initUploder({
        csrf: serverData.csrf,
        title: '上传视频到upuyn',
        file_ext_list: ['.mp4', '.avi'],
        vendor_sign_url: '/video/get_upyun_sign',
        file_save_url: '/video/video_add',
        success_call_back: function (res_file_save) {
            alert('已经上传到了upyun，并且保存到了服务器');
            console.log(res_file_save);
        }
    });
    video_uploader.root_ele.style.zIndex = 1055;

    let image_uploader = initUploder({
        csrf: serverData.csrf,
        title: '上传图片到markedboat',
        file_ext_list: ['image/*'],
        vendor_sign_url: '/upload/get_web_server_sign',
        vendor_upload_ext_params: [{key: '_csrf-frontend', val: serverData.csrf}, {key: '_csrf-backend', val: serverData.csrf}],
        file_save_url: '/upload/file_add',
        success_call_back: function (res_file_save) {
            alert('上传并保存到了服务器，不要费心');
            console.log(res_file_save);
        }
    });
    image_uploader.root_ele.style.zIndex = 1055;

    let file_uploader = initUploder({
        csrf: serverData.csrf,
        title: '上传文件到markedboat',
        file_ext_list: ['*'],
        vendor_sign_url: '/upload/get_web_server_sign',
        vendor_upload_ext_params: [{key: '_csrf-frontend', val: serverData.csrf}, {key: '_csrf-backend', val: serverData.csrf}],
        file_save_url: '/upload/file_add',
        success_call_back: function (res_file_save) {
            alert('上传并保存到了服务器，不要费心');
            console.log(res_file_save);
        }
    });
    file_uploader.root_ele.style.zIndex = 1055;


    let hammerYii2Bootstarp1 = hammerYii2Bootstarp();
    let video_selector = hammerYii2Bootstarp1.modal({title: '选择视频'});
    let image_selector = hammerYii2Bootstarp1.modal({title: '选择图片'});
    let file_selector = hammerYii2Bootstarp1.modal({title: '选择文件'});
    let link_editor = hammerYii2Bootstarp1.modal({title: '填写链接'});
    video_selector.root_ele.style.zIndex = 1050;
    image_selector.root_ele.style.zIndex = 1050;
    file_selector.root_ele.style.zIndex = 1050;
    link_editor.root_ele.style.zIndex = 1050;


    video_selector.setEditorCallback = function (editor_insertVideo_callback) {
        if (!video_selector.funEditorCallback) {
            video_selector.funEditorCallback = editor_insertVideo_callback;
            video_uploader.setCallback(function (res_video_upload) {
                video_selector.callbackEditor(res_video_upload.video_info);
                video_uploader.hide();
                video_selector.hide();
            }, true);
        }
    };
    video_selector.callbackEditor = function (video_info) {
        video_selector.funEditorCallback('http://www.markedboat.com/video/src?video_id=' + video_info.id + '&go=302', video_info.title);
    };

    let div_video_list = new Emt('div');
    let btn_video_upload = new Emt('a').setPros({className: 'btn btn-info', href: '#', textContent: '上传视频'});
    btn_video_upload.addEventListener('click', function () {
        video_uploader.show();
        video_uploader.setCallback(function (res_video_upload) {
            video_selector.callbackEditor(res_video_upload.video_info);
            video_uploader.hide();
            video_selector.hide();
        });
    });
    video_selector.body_ele.addNodes([
        new Emt('p').setPros({className: 'text-left'}).addNodes([
            btn_video_upload
        ]),
        div_video_list
    ]);

    kl.ajax({
        url: input_opt.my_file_video_list_url || '/upload/my_file_list?file_type=video',
        type: 'json',
        method: 'GET',
        success: function (res_my_file) {
            if (res_my_file && res_my_file.status) {
                if (res_my_file.status === 200) {
                    res_my_file.files.forEach(function (video_info) {
                        let btn_video_select = new Emt('a').setPros({className: 'btn btn-default', href: '#', textContent: video_info.title});
                        let video_ele = new Emt('div').setPros({}).addNodes([
                            new Emt('p').setPros({}).addNodes([
                                btn_video_select
                            ]),
                            new Emt('p').setPros({textContent: video_info.detail}),
                        ]);
                        btn_video_select.addEventListener('click', function () {
                            video_selector.callbackEditor(video_info);
                            video_selector.hide();

                        });
                        div_video_list.addNodes([
                            video_ele
                        ])
                    });
                } else {

                }
            }
        },
        error: function (res_my_file) {

        }
    });


    image_selector.setEditorCallback = function (editor_insertImage_callback) {
        if (!image_selector.funEditorCallback) {
            image_selector.funEditorCallback = editor_insertImage_callback;
            image_uploader.setCallback(function (res_image_upload) {
                image_selector.callbackEditor(res_image_upload.file_info);
                image_uploader.hide();
                image_selector.hide();
            }, true);

        }
    };
    image_selector.callbackEditor = function (file_info) {
        image_selector.funEditorCallback('/upload/src?file_id=' + file_info.id + '&file_sets=' + file_info.file_sets_name + '&go=302');
    };

    let div_iamge_list = new Emt('div');
    let btn_iamage_upload = new Emt('a').setPros({className: 'btn btn-info', href: '#', textContent: '上传图片'});
    btn_iamage_upload.addEventListener('click', function () {
        image_uploader.show();

    });

    image_selector.body_ele.addNodes([
        new Emt('p').setPros({className: 'text-left'}).addNodes([
            btn_iamage_upload
        ]),
        div_iamge_list
    ]);

    kl.ajax({
        url: input_opt.my_file_image_list_url || '/upload/my_file_list?file_type=image',
        type: 'json',
        method: 'GET',
        success: function (res_my_file) {
            if (res_my_file && res_my_file.status) {
                if (res_my_file.status === 200) {
                    res_my_file.files.forEach(function (file_info) {
                        let btn_select = new Emt('a').setPros({className: 'btn btn-default', href: '#', textContent: file_info.title});
                        let file_ele = new Emt('div').setPros({}).addNodes([
                            new Emt('p').setPros({}).addNodes([
                                btn_select
                            ]),
                            new Emt('p').setPros({textContent: ''}).addNodes([
                                new Emt('img').setPros({className: 'img-responsive img-thumbnail', src: '/upload/src?file_id=' + file_info.id + '&go=302'})
                            ]),
                            new Emt('p').setPros({textContent: file_info.detail})
                        ]);
                        btn_select.addEventListener('click', function () {
                            image_selector.callbackEditor(file_info);
                            image_selector.hide();

                        });
                        div_iamge_list.addNodes([
                            file_ele
                        ])
                    });
                } else {

                }
            }
        },
        error: function (res_my_file) {

        }
    });


    file_selector.setEditorCallback = function (editor_insertFile_callback) {
        if (!file_selector.funEditorCallback) {
            file_selector.funEditorCallback = editor_insertFile_callback;
            file_uploader.setCallback(function (res_file_upload) {
                file_selector.callbackEditor(res_file_upload.file_info);
                file_uploader.hide();
                file_selector.hide();
            }, true);
        }
    };
    file_selector.callbackEditor = function (file_info) {
        file_selector.funEditorCallback(file_info.title, '/upload/src?file_id=' + file_info.id + '&go=302', file_info.file_sets_name);
    };

    let div_file_list = new Emt('div');
    let btn_file_upload = new Emt('a').setPros({className: 'btn btn-info', href: '#', textContent: '上传文件'});
    btn_file_upload.addEventListener('click', function () {
        console.log(file_uploader);
        file_uploader.show();

    });

    file_selector.body_ele.addNodes([
        new Emt('p').setPros({className: 'text-left'}).addNodes([
            btn_file_upload
        ]),
        div_file_list
    ]);

    kl.ajax({
        url: input_opt.my_file_pdf_list_url || '/upload/my_file_list',
        type: 'json',
        method: 'GET',
        success: function (res_my_file) {
            if (res_my_file && res_my_file.status) {
                if (res_my_file.status === 200) {
                    res_my_file.files.forEach(function (file_info) {
                        let panelHandle = hammerYii2Bootstarp1.panel({title: file_info.title, detail: file_info.detail, footer: ''});
                        let buttonHandel = hammerYii2Bootstarp1.button({title: file_info.title}).opts.color.set('primary');
                        panelHandle.footer_ele.addNodes([buttonHandel.root_ele]);
                        buttonHandel.root_ele.addEventListener('click', function () {
                            file_selector.callbackEditor(file_info);
                            file_selector.hide();
                        });
                        div_file_list.addNodes([
                            panelHandle.root_ele,
                        ])
                    });
                } else {

                }
            }
        },
        error: function (res_my_file) {

        }
    });


    let form_link_handle = hammerYii2Bootstarp1.form();
    form_link_handle.createInputText('url', '输入url', 'url_ele');
    form_link_handle.createInputText('名称', '输入链接名称', 'url_name_ele');
    form_link_handle.createSubmitButton('#', '提交', 'submit_ele');
    link_editor.setEditorCallback = function (editor_insertLink_callback) {
        if (!link_editor.funEditorCallback) {
            link_editor.funEditorCallback = editor_insertLink_callback;
        }
    };
    link_editor.callbackEditor = function (link_url, link_name) {
        link_editor.funEditorCallback(link_url, link_name);
    };
    form_link_handle.submit_ele.addEventListener('click', function () {
        link_editor.callbackEditor(form_link_handle.url_ele.value, form_link_handle.url_name_ele.value);
        link_editor.hide();

    });
    link_editor.body_ele.addNodes([form_link_handle.root_ele]);

    let style_str = '.img-thumbnail {\n' +
        '    padding: 4px;\n' +
        '    line-height: 1.42857143;\n' +
        '    background-color: #fff;\n' +
        '    border: 1px solid #ddd;\n' +
        '    border-radius: 4px;\n' +
        '    -webkit-transition: all 0.2s ease-in-out;\n' +
        '    -o-transition: all 0.2s ease-in-out;\n' +
        '    transition: all 0.2s ease-in-out;\n' +
        '    display: inline-block;\n' +
        '    max-width: 100%;\n' +
        '    height: auto;\n' +
        '    max-height: 300px;' +
        '}' +
        '.modal-content {' +
        '    max-height:' + parseInt(window.innerHeight * 0.9) + 'px;' +
        '    overflow-y:scroll;' +
        '}' +
        'video{' +
        '   max-height:' + parseInt(window.innerHeight * 0.8) + 'px;' +
        '}';
    if (!kl.id('kl-hammer-mixed-editor-style')) {
        document.body.append(new Emt('style').setPros({id: 'kl-hammer-mixed-editor-style', innerHTML: style_str}))
    }
    document.body.append()

    initEditor({
        ui_ele: input_opt.ui_ele,
        data_ele: input_opt.data_ele,
        btns: input_opt.btns,
        funs: {
            video: function (editor_insertVideo_callback) {
                video_selector.show();
                video_selector.setEditorCallback(editor_insertVideo_callback);
                // let video_src = 'http://www.markedboat.com/video/src?video_id=1&go=302';//必须实现  插入的视频 src
                // editor_insertVideo_callback(video_src,video_title);//必须实现 确定了src 之后,回调 editor,让editor插入视频
            },
            img: function (editor_insertImg_callback) {
                image_selector.show();
                image_selector.setEditorCallback(editor_insertImg_callback);
                //let img_src = 'ddd';//必须实现  插入的视频 src
                //img_src = 'https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fupload-images.jianshu.io%2Fupload_images%2F3944667-b4a9e42b46a14946.png&refer=http%3A%2F%2Fupload-images.jianshu.io&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=jpeg?sec=1636867340&t=67e8f8c65afb6d231ef8afa1334035e4';
                //editor_insertImg_callback(img_src);//必须实现 确定了src 之后,回调 editor,让editor插入图片
            },
            file: function (editor_insertFile_callback) {
                file_selector.show();
                file_selector.setEditorCallback(editor_insertFile_callback);

                //let url = 'ddd';//必须实现  插入的视频 src
                //editor_insertFile_callback('file name', 'http://www.baidu.com');//必须实现 确定了src 之后,回调 editor,让editor插入文件
            },
            link: function (editor_insertLink_callback) {
                link_editor.show();
                link_editor.setEditorCallback(editor_insertLink_callback);

                //let url = 'ddd';//必须实现  插入的视频 src
                //editor_insertLink_callback('link name', 'http://www.baidu.com');//必须实现 确定了src 之后,回调 editor,让editor插入链接
            }
        }
    });
};






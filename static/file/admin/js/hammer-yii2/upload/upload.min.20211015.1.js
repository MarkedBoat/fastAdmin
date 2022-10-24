/**
 *
 * @param input_opt  {csrf:'string',title:'title',file_ext_list: ['.mp4', '.avi'], vendor_sign_url:'get_vendor_sign_api' ,vendor_upload_ext_params: [{key: '_csrf-frontend', val: serverData.csrf}] 针对csrf token',file_save_url:'save_file_api', success_call_back:fun(){}}
 * @returns {{hide: hide, show: show}}
 */
let initUploder = function (input_opt) {

    let createInputText = function (label_name, val_name) {
        let input = new Emt('input').setAttrsByStr('type="text" id="kzfileupload-' + val_name + '" class="form-control" name="KzFileUpload[' + val_name + ']"');
        return new Emt('div').setPros({className: 'form-group field-kzfileupload-' + val_name, input_ele: input}).addNodes([
            new Emt('label').setPros({className: 'control-label" for="kzfileupload-' + val_name}).setPros({textContent: label_name}),
            input
        ]);
    };

    let createInputTextArea = function (label_name, val_name) {
        let input = new Emt('textarea').setAttrsByStr('rows="6" id="kzfileupload-' + val_name + '" class="form-control" name="KzFileUpload[' + val_name + ']"');
        return new Emt('div').setPros({className: 'form-group field-kzfileupload-' + val_name, input_ele: input}).addNodes([
            new Emt('label').setPros({className: 'control-label" for="kzfileupload-' + val_name}).setPros({textContent: label_name}),
            input
        ]);
    };


    let div_modal = new Emt('div').setAttrsByStr('class="fade modal" role="dialog" tabindex="-1"').setPros({id: 'modal_' + (parseInt(Math.random(1, 2) * 1000000).toString())});
    //let div_modal = new Emt('div').setAttrsByStr('class="fade modal" role="dialog" tabindex="-1"').setPros({id: 'modal_x'});

    let h5_modal_title = new Emt('h4').setPros({textContent: input_opt.title}).setAttrsByStr('class="modal-title"');
    let form_upload = new Emt('form').addNodes([
        new Emt('input').setAttrsByStr('type="hidden" name="_csrf-frontend"').setPros({value: input_opt.csrf}),
        new Emt('input').setAttrsByStr('type="hidden" name="_csrf-backend"').setPros({value: input_opt.csrf})
    ]);


    form_upload.input_map = {};
    form_upload.input_groups = [];
    [
        {label_name: '标题', val_name: 'title', type: 'text'},
        {label_name: '详情', val_name: 'detail', type: 'textarea'},
        {label_name: '1.文件信息', val_name: 'file_info', type: 'textarea'},
        {label_name: '2.签名信息', val_name: 'sign_info', type: 'textarea'},
        {label_name: '3.cdn信息', val_name: 'vendor_info', type: 'textarea'},

    ].forEach(function (tmp_opt) {
        let form_group = tmp_opt.type === 'text' ? createInputText(tmp_opt.label_name, tmp_opt.val_name) : createInputTextArea(tmp_opt.label_name, tmp_opt.val_name);
        form_upload.input_map[tmp_opt.val_name] = form_group.input_ele;
        form_upload.addNodes([form_group]);
    });
    let input_file = new Emt('input').setAttrsByStr('type="file"  accept="' + input_opt.file_ext_list.join(',') + '"');
    let process_uploading = new Emt('div').setAttrsByStr(' class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%;"');
    let btn_start_upload = new Emt('button').setAttrsByStr('type="button" class="btn btn-primary hide"', '上传');
    div_modal.addNodes([
        new Emt('div').setAttrsByStr('class="modal-dialog"').addNodes([
            new Emt('div').setAttrsByStr('class="modal-content"').addNodes([
                //头部
                new Emt('div').setAttrsByStr('class="modal-header"').addNodes([
                    new Emt('button').setPros({textContent: 'x'}).setAttrsByStr('type="button" class="close" data-dismiss="modal" aria-hidden="true"'),
                    h5_modal_title
                ]),
                //主体
                new Emt('div').setAttrsByStr('class="modal-body"').addNodes([
                    new Emt('div').setAttrsByStr('class="row"').addNodes([
                        new Emt('div').setAttrsByStr('class="col-lg-12"').addNodes([
                            form_upload.addNodes([
                                new Emt('div').setPros({className: 'form-group'}).addNodes([
                                    new Emt('label').setAttrsByStr('class="sr-only" for="inputfile"', '文件选择'),
                                    input_file
                                ]),

                                new Emt('div').setPros({className: 'form-group'}).addNodes([
                                    new Emt('div').setPros({className: 'progress hide'}).addNodes([
                                        process_uploading
                                    ])
                                ]),
                                new Emt('div').setPros({className: 'form-group'}).addNodes([
                                    new Emt('label').setAttrsByStr('class="sr-only" for="inputfile"').setPros({textContent: '文件选择'}),
                                    input_file
                                ]),
                                new Emt('div').setPros({className: 'form-group'}).addNodes([
                                    btn_start_upload
                                ])
                            ])
                        ])
                    ])
                ])

            ])
        ])
    ]);


//{"sign":"3c635a12856d5aa96f5424ac9059f4fe","code":200,"file_size":8665010,"url":"\/fc39\/ae6214e9c7c4b64119f7aa7e676efc39.mp4","time":1634204174,"message":"ok","mimetype":"video\/mp4"}
    let trySaveFileInfo = function () {
        kl.ajax({
            url: input_opt.file_save_url,
            method: "POST",
            type: "json",
            form: new FormData(form_upload),
            httpOkCodes: [400],
            success: function (res_file_save) {
                console.log(res_file_save);
                if (res_file_save.status) {
                    if (res_file_save.status === 200) {
                        alert('保存文件成功，请刷新页面');
                        $('#' + div_modal.id).modal('hide');
                        input_opt.success_call_back(res_file_save);
                    } else {
                        alert('保存文件失败:' + (res_file_save.message || '未知'))
                    }
                } else {
                    alert('保存文件 网络异常')
                }
            },
            error: function (res_file_save) {
                console.log(res_file_save);
                alert('网络错误！');
            }
        });
        return false;
    };

    let tryUpload = function (sign_info, data_for_sign) {
        let fd = new FormData();
        fd.append('policy', sign_info.post.policy);
        fd.append('signature', sign_info.post.signature);
        fd.append("file", input_file.files[0]);
        fd.append("url", sign_info.url);
        console.log(input_opt.vendor_upload_ext_params);
        if (input_opt.vendor_upload_ext_params && typeof input_opt.vendor_upload_ext_params.forEach === 'function') {
            input_opt.vendor_upload_ext_params.forEach(function (param) {
                fd.append(param.key, param.val);
            })
        }
        let xhrUpyun = new XMLHttpRequest();
        xhrUpyun.addEventListener("load", function () {
            console.log(xhrUpyun);
            let dataUpyun = JSON.parse(xhrUpyun.responseText);
            console.log(dataUpyun, sign_info);
            //{"sign":"3c635a12856d5aa96f5424ac9059f4fe","code":200,"file_size":8665010,"url":"\/fc39\/ae6214e9c7c4b64119f7aa7e676efc39.mp4","time":1634204174,"message":"ok","mimetype":"video\/mp4"}
            if (dataUpyun.message && dataUpyun.message == 'ok') {
                console.log('文件上传成功', sign_info);
                // callbackUpload(data);
                form_upload.input_map.vendor_info.value = xhrUpyun.responseText;
                trySaveFileInfo(dataUpyun, sign_info, data_for_sign);
            }
        }, false);


        xhrUpyun.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                process_uploading.style.width = parseInt(evt.loaded * 100 / evt.total).toString() + '%';
            }
        }, false);


        xhrUpyun.addEventListener("error", function () {
            console.log('出错了');
        }, false);
        xhrUpyun.addEventListener("abort", function () {
            console.log('中断了');
        }, false);

        xhrUpyun.open("POST", sign_info.api);
        xhrUpyun.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        xhrUpyun.send(fd);
    };

    let getSign = function () {
        kl.ajax({
            url: input_opt.vendor_sign_url,
            method: "POST",
            type: "json",
            form: new FormData(form_upload),
            httpOkCodes: [400],
            success: function (res_get_upyun_sign) {
                console.log(res_get_upyun_sign);
                if (res_get_upyun_sign.status) {
                    if (res_get_upyun_sign.status === 200) {
                        alert('前置信息检查已经通过，可以上传了');
                        if (res_get_upyun_sign.res.url && res_get_upyun_sign.res.api) {
                            form_upload.input_map.sign_info.value = JSON.stringify(res_get_upyun_sign.res);
                            process_uploading.parentElement.classList.remove('hide');
                            btn_start_upload.classList.remove('hide');
                            btn_start_upload.onclick = function () {
                                tryUpload(res_get_upyun_sign.res, res_get_upyun_sign.input_data);
                            };
                        }
                    } else {
                        alert('保存失败:' + (res_get_upyun_sign.message || '未知'))
                    }
                } else {
                    alert('网络异常')
                }
            },
            error: function (res_get_upyun_sign) {
                console.log(res_get_upyun_sign);
                alert('网络错误！');
            }
        });
        return false;
    };

    input_file.addEventListener('change', function () {
        btn_start_upload.classList.add('hide');
        console.log(this.files[0]);
        var fr = new FileReader();
        fr.file = this.files[0];
        fr.onload = function (evt) {
            let md5 = hex_md5(evt.target.result);
            console.log('file hash hex_md5', md5);
            form_upload.input_map.title.value = form_upload.input_map.title.value + ' ' + fr.file.name;
            form_upload.input_map.detail.value = fr.file.name + "\n" + form_upload.input_map.title.value;
            form_upload.input_map.file_info.value = JSON.stringify({
                md5: md5,
                type: fr.file.type,
                size: fr.file.size,
                name: fr.file.name,
                lastModifiedDate: fr.file.lastModifiedDate,
            });
            getSign();
        };
        //fr.readAsDataURL(tmp_img);
        fr.readAsBinaryString(this.files[0]);
        //fr.readAsText(this.files[0]);
        //fr.readAsArrayBuffer(this.files[0]);
    });

    document.body.append(new Emt('div').addNodes([div_modal]));

    return {
        show: function () {
            $('#' + div_modal.id).modal('show');
        },
        hide: function () {
            $('#' + div_modal.id).modal('hide');
        },
        setCallback: function (success_call_back, force) {
            if (typeof input_opt.success_call_back === "function" && !force) {
                console.log('uploader的callback 未被重新设置');
                return false;
            }
            input_opt.success_call_back = success_call_back;
            console.log('uploader的callback 被重新定义');

        },
        root_ele: div_modal
    };

};


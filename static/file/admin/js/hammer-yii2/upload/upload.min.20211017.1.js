/**
 *
 * @param input_opt  {
 * csrf:'string',
 * title:'title',
 * file_ext_list: ['.mp4', '.avi'],
 * vendor_sign_url:'get_vendor_sign_api' ,
 * vendor_upload_ext_params: [{key: '_csrf-frontend', val: serverData.csrf}] 针对csrf token',
 * file_save_url:'save_file_api',
 * signOkNotifyFun:fun(){},回调通知没参数
 * signErrorNotifyFun:function(messge){},前置检查(签名，文件是否存在)错误 回调
 *
 * uploadOkNotifyFun:fun(){},回调通知没参数
 * uploadErrorNotifyFun:function(messge){},前置检查(签名，文件是否存在)错误 回调
 *
 * saveOkNotifyFun:fun(){},回调通知没参数
 * saveErrorNotifyFun:function(messge){},前置检查(签名，文件是否存在)错误 回调
 * }
 * @returns {{hide: hide, show: show}}
 */
let initUploder = function (input_opt) {
    let handle_this = {
        __doc: {
            csrf: '',
            title: '',
            file_ext_list: '',
            vendor_sign_url: '',
            vendor_upload_ext_params: '',
            file_save_url: '',
            signOkNotifyFun: '',
            signErrorNotifyFun: '',
            uploadOkNotifyFun: '',
            uploadErrorNotifyFun: '',
            saveOkNotifyFun: '',
            saveErrorNotifyFun: '',
            handle_modal: '',
            handle_form: '',
        }
    };
    for (var k in handle_this.__doc) {
        if (typeof input_opt[k] !== 'undefined') {
            handle_this[k] = input_opt[k];
        }
    }

    let hammerYii2Bootstarp1 = hammerYii2Bootstarp();


    if (!handle_this.handle_modal) {
        handle_this.handle_modal = hammerYii2Bootstarp1.modal();
    }

    if (!handle_this.handle_form) {

        handle_this.handle_form = hammerYii2Bootstarp1.form({name_tpl: 'KzFileUpload[$name_key]'});

        handle_this.handle_form.createInputHide('input_csrf_ele').input_csrf_ele.setPros({name: '_csrf-frontend', value: handle_this.csrf});
        handle_this.handle_form.createInputHide('input_csrf_ele2').input_csrf_ele2.setPros({name: '_csrf-backend', value: handle_this.csrf});

        handle_this.handle_form.createInputText('标题', '', 'input_title_ele', 'title');//注意，这些返回的是 handle,如果想用ele,想input_csrf_ele 把handle_ele_key 作为属性即可
        handle_this.handle_form.createInputTextArea('描述', '', 'input_detail_ele', 'detail');
        handle_this.handle_form.createInputTextArea('1.文件信息', '', 'input_file_info_ele', 'file_info');
        handle_this.handle_form.createInputTextArea('2.签名信息', '', 'input_sign_info_ele', 'sign_info');
        handle_this.handle_form.createInputTextArea('3.cdn信息', '', 'input_vendor_info_ele', 'vendor_info');

        handle_this.handle_form.createInputFile('选择文件', handle_this.file_ext_list, 'file_ele');
        handle_this.handle_form.createProcess('', 'process_ele').process_ele.hide();
        handle_this.handle_form.createSubmitButton('', '开始上传', 'btn_sumit_ele').btn_sumit_ele;


        handle_this.handle_modal.body_ele.addNodes([
            handle_this.handle_form.root_ele
        ])
    }


//{"sign":"3c635a12856d5aa96f5424ac9059f4fe","code":200,"file_size":8665010,"url":"\/fc39\/ae6214e9c7c4b64119f7aa7e676efc39.mp4","time":1634204174,"message":"ok","mimetype":"video\/mp4"}
    handle_this.trySaveFileInfo = function () {
        kl.ajax({
            url: handle_this.file_save_url,
            method: "POST",
            type: "json",
            form: new FormData(handle_this.handle_form.root_ele),
            httpOkCodes: [400],
            success: function (res_file_save) {
                console.log(res_file_save);
                if (res_file_save.status) {
                    if (res_file_save.status === 200) {

                        if (typeof handle_this.saveOkNotifyFun === 'undefined') {
                            alert('保存文件成功，请刷新页面');
                            //handle_this.success_call_back(res_file_save);
                        } else if (typeof handle_this.saveOkNotifyFun === 'function') {
                            handle_this.saveOkNotifyFun(res_file_save);
                        }
                        handle_this.handle_modal.hide();
                    } else {
                        if (typeof handle_this.saveErrorNotifyFun === 'function') {
                            handle_this.saveErrorNotifyFun(res_file_save.message || '未知');
                        } else {
                            alert('保存文件失败:' + (res_file_save.message || '未知'))
                        }
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

    handle_this.tryUpload = function (file_obj, sign_info, data_for_sign) {
        let fd = new FormData();
        fd.append('policy', sign_info.post.policy);
        fd.append('signature', sign_info.post.signature);
        fd.append("file", file_obj);
        fd.append("url", sign_info.url);
        if (handle_this.vendor_upload_ext_params && typeof handle_this.vendor_upload_ext_params.forEach === 'function') {
            handle_this.vendor_upload_ext_params.forEach(function (param) {
                fd.append(param.key, param.val);
            })
        }
        let xhfUpload = new XMLHttpRequest();
        xhfUpload.addEventListener("load", function () {
            console.log(xhfUpload);
            let dataUpyun = JSON.parse(xhfUpload.responseText);
            console.log(dataUpyun, sign_info);
            //{"sign":"3c635a12856d5aa96f5424ac9059f4fe","code":200,"file_size":8665010,"url":"\/fc39\/ae6214e9c7c4b64119f7aa7e676efc39.mp4","time":1634204174,"message":"ok","mimetype":"video\/mp4"}
            if (dataUpyun.message && dataUpyun.message == 'ok') {
                console.log('文件上传成功', sign_info);
                handle_this.handle_form.input_vendor_info_ele.value = xhfUpload.responseText;
                if (typeof handle_this.uploadOkNotfiFun === 'function') {
                    handle_this.uploadOkNotfiFun();
                }

                handle_this.trySaveFileInfo();
            } else {
                console.log('上传失败', dataUpyun, sign_info);
                if (typeof handle_this.uploadFailNotfiFun === 'function') {
                    handle_this.uploadFailNotfiFun();
                } else {
                    alert('上传失败');
                }
            }
        }, false);


        xhfUpload.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                if (handle_this.handle_form.process_ele) {
                    handle_this.handle_form.process_ele.style.width = parseInt(evt.loaded * 100 / evt.total).toString() + '%';
                } else {
                    console.log(evt.loaded, evt.total, evt.loaded / evt.total);
                }
            }
        }, false);


        xhfUpload.addEventListener("error", function () {
            if (typeof handle_this.uploadFailNotfiFun === 'function') {
                handle_this.uploadFailNotfiFun('出错了');
            }
            console.log('出错了');
        }, false);
        xhfUpload.addEventListener("abort", function () {
            if (typeof handle_this.uploadFailNotfiFun === 'function') {
                handle_this.uploadFailNotfiFun('中断了');
            }
            console.log('中断了');
        }, false);

        xhfUpload.open("POST", sign_info.api);
        xhfUpload.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        xhfUpload.send(fd);
    };

    handle_this.getSign = function (file_obj) {
        kl.ajax({
            url: handle_this.vendor_sign_url,
            method: "POST",
            type: "json",
            form: new FormData(handle_this.handle_form.root_ele),
            httpOkCodes: [400],
            success: function (res_get_sign) {
                console.log(res_get_sign);
                if (res_get_sign.status) {
                    if (res_get_sign.status === 200) {
                        if (typeof handle_this.signOkNotifyFun === 'undefined') {
                            alert('前置信息检查已经通过，可以上传了');
                        } else if (typeof handle_this.signOkNotifyFun === 'function') {
                            handle_this.signOkNotifyFun();
                        }
                        if (res_get_sign.res.url && res_get_sign.res.api) {
                            handle_this.handle_form.input_sign_info_ele.value = JSON.stringify(res_get_sign.res);
                            handle_this.handle_form.process_ele.show();
                            handle_this.handle_form.btn_sumit_ele.classList.remove('hide');
                            handle_this.handle_form.btn_sumit_ele.onclick = function () {
                                console.log('点击了');
                                handle_this.tryUpload(file_obj, res_get_sign.res, res_get_sign.input_data);
                                //e.preventDefault();
                            };
                        }
                    } else {
                        if (typeof handle_this.signErrorNotifyFun === 'function') {
                            handle_this.signErrorNotifyFun();
                        } else {
                            alert('保存失败:' + (res_get_sign.message || '未知'));
                        }
                    }
                } else {
                    alert('网络异常')
                }
            },
            error: function (res_get_sign) {
                console.log(res_get_sign);
                alert('网络错误！');
            }
        });
        return false;
    };

    //File 类型 ,this.files[0]这种
    handle_this.loadInputFile = function (input_file) {
        var fr = new FileReader();
        fr.onload = function (evt) {
            let md5 = hex_md5(evt.target.result);
            console.log('file hash hex_md5', md5, input_file);
            handle_this.handle_form.input_title_ele.value = handle_this.handle_form.input_title_ele.value + ' ' + input_file.name;
            handle_this.handle_form.input_detail_ele.value = input_file.name + "\n" + handle_this.handle_form.input_detail_ele.value;
            handle_this.handle_form.input_file_info_ele.value = JSON.stringify({
                md5: md5,
                type: input_file.type,
                size: input_file.size,
                name: input_file.name,
                lastModifiedDate: input_file.lastModifiedDate,
            });
            handle_this.getSign(input_file);
        };
        fr.readAsBinaryString(input_file);
    }


    if (handle_this.handle_form.file_ele) {
        handle_this.handle_form.file_ele.addEventListener('change', function () {
            btn_start_upload.classList.add('hide');
            console.log('input file change', this.files[0]);
            handle_this.loadInputFile(this.files[0]);
        });
    }


    handle_this.setCallback = function (success_call_back, force) {
        if (typeof handle_this.success_call_back === "function" && !force) {
            console.log('uploader的callback 未被重新设置');
            return false;
        }
        handle_this.success_call_back = success_call_back;
        console.log('uploader的callback 被重新定义');
    },


        handle_this.show = function () {
            handle_this.handle_modal.show();
        }
    handle_this.hide = function () {
        handle_this.handle_modal.hide();
    }


    return handle_this;

};


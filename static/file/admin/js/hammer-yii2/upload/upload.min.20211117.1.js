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
            title: '弹窗标题',
            file_ext_list: '',
            vendors: [
                {
                    name: '厂商名',
                    get_sign_url: '获取签名和文件是否存在的url,提供后续上传api',
                    match_keyword: {
                        file_types: '[filetype关系词1]',
                        exts: '[后缀关键词1]',
                    },
                    action: {
                        sign: {
                            url: '！获取签名和文件是否存在的url,提供后续上传api',
                            ext_params: '？额外参数map,值为false代表不适用',
                            notifyOk: '成功回调函数',
                            notifyError: '失败回调函数'
                        },
                        upload: {
                            url: '？上传文件的api,后端提供的优先',
                            ext_params: '？额外参数map,值为false代表不适用',
                            notifyOk: '成功回调函数',
                            notifyError: '失败回调函数'
                        },
                        save: {
                            url: '！保存文件的',
                            ext_params: '？额外参数map,值为false代表不适用',
                            notifyOk: '成功回调函数',
                            notifyError: '失败回调函数'
                        }
                    },
                }
            ],
            handle_modal: '',
            handle_form: '',
            vendor_index: 0,
            current_vendor: {}
        }
    };

    let tmp_fun = function (handle_obj, input_obj, doc_obj) {
        for (var k in doc_obj) {
            if (typeof handle_this.__doc[k] === 'object') {
                if (typeof input_opt[k] === 'undefined') {
                    //handle_obj[k] = {};
                    //tmp_fun(handle_obj[k], input_obj[k], doc_obj[k]);
                } else {
                    handle_obj[k] = input_obj[k];
                    tmp_fun(handle_obj[k], input_obj[k], doc_obj[k]);
                }
            } else {
                if (typeof input_obj[k] !== 'undefined') {
                    handle_obj[k] = input_obj[k];
                }
            }
        }
    }
    tmp_fun(handle_this, input_opt, handle_this.__doc);
    console.log(handle_this);

    let makeFormdata = function (form_ele, params) {
        var formData = new FormData(form_ele);
        if (params && params.forEach) {
            params.forEach(function (param) {
                formData.append(param.key, param.val);
            })
        }
        return formData;
    }

    let hammerYii2Bootstarp1 = hammerYii2Bootstarp();


    if (!handle_this.handle_modal) {
        handle_this.handle_modal = hammerYii2Bootstarp1.modal();
    }

    if (!handle_this.handle_form) {

        handle_this.handle_form = hammerYii2Bootstarp1.form({name_tpl: 'KzFileUpload[$name_key]'});


        // handle_this.handle_form.createInputHide('input_csrf_ele').input_csrf_ele.setPros({name: '_csrf-frontend', value: handle_this.csrf});

        handle_this.handle_form.createInputText('标题', '', 'input_title_ele', 'title');//注意，这些返回的是 handle,如果想用ele,想input_csrf_ele 把handle_ele_key 作为属性即可
        handle_this.handle_form.createInputTextArea('描述', '', 'input_detail_ele', 'detail');

        let tmp_ar = [];
        handle_this.vendors.forEach(function (vendor_info, tmp_index) {
            tmp_ar.push({label: vendor_info.name, value: tmp_index});
        });
        handle_this.handle_form.createSelect('选择存储位置', tmp_ar, 'vendor_select_ele');

        handle_this.handle_form.createInputTextArea('1.文件信息', '', 'input_file_info_ele', 'file_info');
        handle_this.handle_form.createInputText('文件md5值', '', 'input_file_md5_ele');

        handle_this.handle_form.createInputTextArea('2.签名信息', '', 'input_sign_info_ele', 'sign_info');
        handle_this.handle_form.createInputTextArea('3.保存信息', '', 'input_store_info_ele', 'store_info');
        handle_this.handle_form.createInputTextArea('4.cdn信息', '', 'input_vendor_info_ele', 'vendor_info');

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
            url: handle_this.current_vendor.action.save.url,
            method: "POST",
            type: "json",
            form: makeFormdata(handle_this.handle_form.root_ele, handle_this.current_vendor.action.save.ext_params || []),
            httpOkCodes: [400],
            success: function (res_file_save) {
                console.log(res_file_save);
                if (res_file_save.status) {
                    if (res_file_save.status === 200) {

                        if (typeof handle_this.current_vendor.action.notifyOk === 'undefined') {
                            alert('保存文件成功，请刷新页面');
                            //handle_this.success_call_back(res_file_save);
                        } else if (typeof handle_this.current_vendor.action.save.notifyOk === 'function') {
                            handle_this.current_vendor.action.save.notifyOk(res_file_save);
                        }
                        handle_this.handle_modal.hide();
                    } else {
                        if (typeof handle_this.current_vendor.action.save.notifyError === 'function') {
                            handle_this.current_vendor.action.save.notifyError(res_file_save.message || '未知');
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
        fd.append('storeinfo', JSON.stringify(sign_info.store_openinfo));

        if (handle_this.current_vendor.action.upload.ext_params && typeof handle_this.current_vendor.action.upload.ext_params.forEach === 'function') {
            handle_this.current_vendor.action.upload.ext_params.forEach(function (param) {
                fd.append(param.key, param.val);
            })
        }
        let xhfUpload = new XMLHttpRequest();
        xhfUpload.timeout = 3600 * 1000;
        xhfUpload.addEventListener("load", function () {
            console.log(xhfUpload);
            let dataUpyun = JSON.parse(xhfUpload.responseText);
            console.log(dataUpyun, sign_info);
            //{"sign":"3c635a12856d5aa96f5424ac9059f4fe","code":200,"file_size":8665010,"url":"\/fc39\/ae6214e9c7c4b64119f7aa7e676efc39.mp4","time":1634204174,"message":"ok","mimetype":"video\/mp4"}
            if (dataUpyun.message && dataUpyun.message == 'ok') {
                console.log('文件上传成功', sign_info);
                handle_this.handle_form.input_vendor_info_ele.value = xhfUpload.responseText;
                if (typeof handle_this.current_vendor.action.upload.notifyOk === 'function') {
                    handle_this.current_vendor.action.upload.notifyOk();
                }

                handle_this.trySaveFileInfo();
            } else {
                console.log('上传失败', dataUpyun, sign_info);
                if (typeof handle_this.current_vendor.action.upload.notifyError === 'function') {
                    handle_this.current_vendor.action.upload.notifyError();
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
            if (typeof handle_this.current_vendor.action.upload.notifyError === 'function') {
                handle_this.current_vendor.action.upload.notifyError('出错了');
            }
            console.log('出错了');
        }, false);
        xhfUpload.addEventListener("abort", function () {
            if (typeof handle_this.current_vendor.action.upload.notifyError === 'function') {
                handle_this.current_vendor.action.upload.notifyError('中断了');
            }
            console.log('中断了');
        }, false);

        xhfUpload.open("POST", sign_info.api);
        xhfUpload.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        xhfUpload.send(fd);
    };

    handle_this.getSign = function (file_obj) {
        kl.ajax({
            url: handle_this.current_vendor.action.sign.url,
            method: "POST",
            type: "json",
            form: makeFormdata(handle_this.handle_form.root_ele, handle_this.current_vendor.action.sign.ext_params),
            httpOkCodes: [400],
            success: function (res_get_sign) {
                console.log(res_get_sign);
                if (res_get_sign.status) {
                    if (res_get_sign.status === 200) {
                        if (typeof handle_this.current_vendor.action.sign.notifyOk === 'undefined') {
                            alert('前置信息检查已经通过，可以上传了');
                        } else if (typeof handle_this.current_vendor.action.sign.notifyOk === 'function') {
                            handle_this.current_vendor.action.sign.notifyOk();
                        }
                        if (res_get_sign.res.url && res_get_sign.res.api) {
                            handle_this.handle_form.input_sign_info_ele.value = JSON.stringify(res_get_sign.res);
                            handle_this.handle_form.input_store_info_ele.value = JSON.stringify(res_get_sign.res.store_openinfo);
                            handle_this.handle_form.process_ele.show();
                            handle_this.handle_form.btn_sumit_ele.classList.remove('hide');
                            handle_this.handle_form.btn_sumit_ele.onclick = function () {
                                console.log('点击了');
                                handle_this.tryUpload(file_obj, res_get_sign.res, res_get_sign.input_data);
                                //e.preventDefault();
                            };
                        }
                    } else {
                        if (typeof handle_this.current_vendor.action.sign.notifyError === 'function') {
                            handle_this.current_vendor.action.sign.notifyError();
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

    handle_this.selectVendor = function (vendor_index) {
        handle_this.handle_form.vendor_select_ele.value = vendor_index;
        handle_this.current_vendor = handle_this.vendors[vendor_index];
        handle_this.vendor_index = vendor_index;
    }
    handle_this.selectVendor(0);


    //File 类型 ,this.files[0]这种
    handle_this.loadInputFile = function (input_file, file_md5_str) {
        var fr = new FileReader();
        fr.onload = function (evt) {
            let md5str = hex_md5(evt.target.result);
            //console.log( 'str_md5',str_md5(evt.target.result));
            //console.log( 'b64_md5',str_md5(evt.target.result));
            //console.log('_md5', md5(evt.target.result));
            console.log();
            if (md5str === 'd41d8cd98f00b204e9800998ecf8427e') {
                //alert('计算md5 失败');
                if (file_md5_str && file_md5_str.length === 32) {
                    md5str = file_md5_str;
                } else {
                    md5str = window.prompt('计算md5 失败,请手动填写', '');
                    if (!md5str) {
                        return false;
                    }
                }

            }

            console.log('file hash hex_md5', md5str, input_file);
            handle_this.handle_form.input_title_ele.value = handle_this.handle_form.input_title_ele.value + ' ' + input_file.name;
            handle_this.handle_form.input_detail_ele.value = input_file.name + "\n" + handle_this.handle_form.input_detail_ele.value;
            handle_this.handle_form.input_file_info_ele.value = JSON.stringify({
                md5: md5str,
                type: input_file.type,
                size: input_file.size,
                name: input_file.name,
                lastModifiedDate: input_file.lastModifiedDate,
            });
            handle_this.getSign(input_file);

        };
        fr.readAsBinaryString(input_file);
        //fr.readAsText(input_file);
    }


    if (handle_this.handle_form.file_ele) {
        handle_this.handle_form.file_ele.addEventListener('change', function () {
            handle_this.handle_form.btn_sumit_ele.classList.add('hide');
            console.log('input file change', this.files[0]);
            handle_this.loadInputFile(this.files[0], handle_this.handle_form.input_file_md5_ele.value);
        });
    }
    handle_this.handle_form.vendor_select_ele.addEventListener('change', function () {
        handle_this.handle_form.btn_sumit_ele.classList.add('hide');
        console.log('vendors change', handle_this.handle_form.file_ele.files[0]);
        handle_this.selectVendor(this.value);
        handle_this.loadInputFile(handle_this.handle_form.file_ele.files[0], handle_this.handle_form.input_file_md5_ele.value);
    });


    handle_this.setCallback = function (success_call_back, force) {
        if (typeof handle_this.success_call_back === "function" && !force) {
            console.log('uploader的callback 未被重新设置');
            return false;
        }
        handle_this.success_call_back = success_call_back;
        console.log('uploader的callback 被重新定义');
    }


    handle_this.show = function () {
        handle_this.handle_modal.show();
    }
    handle_this.hide = function () {
        handle_this.handle_modal.hide();
    }


    return handle_this;

};


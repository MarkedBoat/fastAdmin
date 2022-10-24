/**
 *
 * @param param
 *
 *   url:
 *   method:  POST,
 *   data  {},
 *   from fromData,
 *   type  'json',
 *   isCors 是否跨域
 *   onResOk 结果正常
 *   onResError 结果错误
 *   onServerError 服务器异常
 *   onNetworError 网络错误
 */
let requestBackend = function (param) {

    let newfromData = new FormData();

    let data2form = function (fromData, input_data, level, name_root) {
        if (level === 0) {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    data2form(fromData, input_data[k], 1, k);
                } else {
                    fromData.append(k, input_data[k]);
                }
            }
        } else {
            for (let k in input_data) {
                if (typeof input_data[k] === 'object') {
                    data2form(fromData, input_data[k], level + 1, name_root + '[' + k + ']');
                } else {
                    fromData.append(name_root + '[' + k + ']', input_data[k]);
                }
            }
        }
    };

    if ((param.method || 'POST') === 'POST') {
        if (serverData && serverData.csrf) {
            newfromData.append('_csrf-backend', serverData.csrf);
            newfromData.append('_csrf-frontend', serverData.csrf);
        }
    }
    data2form(newfromData, param.data, 0, '');
    console.log(newfromData,newfromData.get('_csrf-backend'),newfromData.get('q'),param.data,param.from);
    kl.ajax({
        url: param.url,
        method: param.method || 'POST',
        data: param.data || {},
        form: param.form || newfromData,
        type: param.type || 'json',
        isAjax: !param.isCors,
        success: function (res) {
            if ((param.type || 'json') === 'json') {
                if (res.status) {
                    if (res.status === 200) {
                        typeof param.onResOk === 'function' ? param.onResOk(res.data) : alert('响应正常，但未定义:onResOk');
                    } else if (res.status === 400) {
                        typeof param.onResError === 'function' ? param.onResError(res.msg || '400msg异常') : alert('响应失败，未定义:onResError');
                    } else {
                        typeof param.onServerError === 'function' ? param.onServerError('服务器错误，code异常') : alert('服务器错误,code异常，未定义:onServerError');
                    }
                } else {
                    typeof param.onServerError === 'function' ? param.onServerError('服务器错误,结构异常') : alert('服务器错误,结构异常，未定义:onServerError');
                }
            } else {
                typeof param.onResOk === 'function' ? param.onResOk(res) : alert('未定义:onResOk');
            }
        },
        error: function () {
            typeof param.onNetworError === 'function' ? param.onNetworError('网络异常') : alert('网络异常');
        }
    })
};
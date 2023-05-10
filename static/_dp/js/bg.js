let bg_init = function (page_init_fun) {
    let utk = localStorage.getItem('utk');
    let loginUrl = '/_dp/v1/user/login.html';
    if (!utk) {
        alert('未登录，自动到登录');
        document.location = loginUrl;
        // throw  '查看';
    }
    if (window.serverData === undefined) {
        let ar = document.location.href.split('?');

        if (ar.length !== 2) {
            //alert('丢失参数错误');
            //throw  '查看';
            window.serverData = {};
        } else {
            console.log(ar[1], ar[1].urldecode());
            let ar2 = ar[1].split('#');
            try {
                window.serverData = JSON.parse(ar2[0].urldecode());
                //serverData = JSON.parse(ar[1]);
            } catch (e) {
                alert('参数格式错误');
                console.log(e, e.message);
                throw  '查看';
            }
        }

    }


    window.serverData.table = {};
    window.serverData.columns = {};
    window.serverData.vals_map = {};
    window.serverData.vals_range_map = {};


    kl.ajax({
        url: '/_dp/v1/user/info',
        data: {},
        method: 'POST',
        success: function (admin_info_res) {
            console.log(admin_info_res);
            if (admin_info_res.status) {
                if (admin_info_res.status === 200) {
                    window.utk = utk;
                    window.adminInfo = admin_info_res.data;
                    page_init_fun();

                } else {
                    if (admin_info_res.code.indexOf('user_error_token') === 0) {
                        alert('未登录，跳转到登录');
                        document.location = loginUrl;
                    } else {
                        alert('失败:' + (admin_info_res.msg || '未知'))
                    }
                }
            } else {
                alert('数据异常')
            }
        },
        error: function (res_share) {
            console.log(res_share);
            alert('网络错误！');
        },
        type: 'json',
    });
};
let bg_init = function (page_init_fun) {
    let utk = localStorage.getItem('utk');
    if (!utk) {
        alert('未登录，自动到登录');
        document.location = '/admin/login.html';
        // throw  '查看';
    }
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


    window.serverData.table = {};
    window.serverData.columns = {};
    window.serverData.vals_map = {};
    window.serverData.vals_range_map = {};


    let tmp_list = function (obj) {
        console.log(obj);


        let li = new Emt('li');

        if (obj.sub_menus.length > 0) {
            li.addNodes([
                new Emt('span').setPros({textContent: '+' + obj.title})
            ]);

            let ul = new Emt('ul');
            obj.sub_menus.forEach(function (sub_info) {
                ul.addNodes([tmp_list(sub_info)]);
            });
            li.classList.add('hide_child_li');
            li.addEventListener('click', function (e) {
                //e.preventDefault();
                e.stopPropagation();
                li.classList.toggle('hide_child_li');
            });
            li.addNode(ul);
        } else {
            li.addNodes([
                new Emt('a').setPros({textContent: obj.title, href: kl.isUndefined(obj, 'opts.link') ? '#' : obj.opts.link})
            ]);
        }
        return li;
    };


    let getMenus = function () {
        kl.ajax({
            url: '/dp/v1/admin/rbac/menu?user_token=' + utk,
            data: {},
            method: 'POST',
            success: function (res_menu) {
                console.log(res_menu);
                if (res_menu.status) {
                    if (res_menu.status === 200) {
                        let btn = new Emt('span', '', '##导航##');
                        let menus_box = new Emt('div', 'class="menus_box hide"').addNodes([
                            function (ul) {
                                res_menu.data.forEach(function (tree) {
                                    ul.addNode(tmp_list(tree));
                                });
                                return ul;
                            }(new Emt('ul'))
                        ]);
                        kl.id('bg_menus_div').innerHTML = '';
                        kl.id('bg_menus_div').append(btn);
                        kl.id('bg_menus_div').append(menus_box);
                        btn.addEventListener('click', function () {
                            menus_box.classList.toggle('hide');
                        });
                        page_init_fun();
                    } else {
                        alert('失败:' + (res_menu.msg || '未知'))
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

    kl.ajax({
        url: '/dp/v1/admin/user/Info?user_token=' + utk,
        data: {},
        method: 'POST',
        success: function (admin_info_res) {
            console.log(admin_info_res);
            if (admin_info_res.status) {
                if (admin_info_res.status === 200) {
                    window.utk = utk;
                    window.adminInfo = admin_info_res.data;
                    getMenus()
                } else {
                    if (admin_info_res.code.indexOf('user_error_token') === 0) {
                        alert('未登录，跳转到登录');
                        document.location = '/admin/login.html';
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
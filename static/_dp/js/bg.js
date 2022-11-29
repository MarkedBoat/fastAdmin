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


    let rander_tree = function (dataTree) {

        let menu_root_div = new Emt('div', 'class="_dp_menu_root_div"');
        menu_root_div.apiHandle = {
            menu: {list: [], map: {}},
        };
        menu_root_div.createMenuDiv = function () {
            let menu_div = new Emt('div', 'class="_dp_menu_div"');
            menu_div.apiHandle = {};

            menu_div.apiHandle.title_td = new Emt('div', 'class="_dp_title_td"');
            menu_div.apiHandle.body_div = new Emt('div', 'class="_dp_menu_body_div"');
            menu_div.apiHandle.body_div.apiHandle = menu_div.apiHandle;
            menu_div.apiHandle.menus_div = new Emt('div', 'class="_dp_menus_div"');

            menu_div.addNodes([
                menu_div.apiHandle.body_div.addNodes([
                    menu_div.apiHandle.title_td,
                ]),
                menu_div.apiHandle.menus_div.addNodes([]),
            ]);

            menu_div.apiHandle.setMenuInfo = function (menuInfo) {
                menu_div.apiHandle.data = menuInfo;

                if (menuInfo.sub_menus.length === 0) {
                    menu_div.apiHandle.title_td.append(new Emt('a').setPros({target: '_blank', textContent: menuInfo.title, href: kl.isUndefined(menuInfo, 'opts.link') ? '#' : menuInfo.opts.link}));
                } else {
                    let title_div = new Emt('div', 'class="_dp_menu_title_div"');
                    title_div.addNode(new Emt('div', '', menuInfo.title));
                    menu_div.apiHandle.title_td.append(title_div);
                }
            };

            return menu_div;
        };


        menu_root_div.appendMenuTreeNode = function (menuInfo) {
            let menu_div = menu_root_div.createMenuDiv();
            menu_root_div.apiHandle.menu.list.push(menu_div);
            menu_root_div.apiHandle.menu.map[menuInfo.id] = menu_div;
            if (menuInfo.pid == '0') {
                menu_root_div.addNodes([menu_div]);
            } else {
                if (menu_root_div.apiHandle.menu.map[menuInfo.pid] === undefined) {
                    menu_div.classList.add('pid_not_found');
                    menu_root_div.addNodes([menu_div]);
                } else {
                    menu_root_div.apiHandle.menu.map[menuInfo.pid].apiHandle.menus_div.addNode(menu_div);
                }
            }
            menu_div.apiHandle.setMenuInfo(menuInfo);
            if (menuInfo.sub_menus.length === 0) {
                menu_div.apiHandle.menus_div.remove();

            } else {
                menuInfo.sub_menus.forEach(function (menuInfo2, tmp_index) {
                    menuInfo2.pathName = menuInfo.pathName + '/' + menuInfo2.title;
                    menu_root_div.appendMenuTreeNode(menuInfo2);
                });
            }
        };
        dataTree.forEach(function (menuInfo) {
            menuInfo.pathName = '/' + menuInfo.title;
            menu_root_div.appendMenuTreeNode(menuInfo);
        });
        return menu_root_div;
    };


    let getMenus = function () {
        kl.ajax({
            url: '/_dp/v1/rbac/menu?user_token=' + utk,
            data: {},
            method: 'POST',
            success: function (res_menu) {
                console.log(res_menu);
                if (res_menu.status) {
                    if (res_menu.status === 200) {
                        let btn = kl.id('menu_toggle_btn');
                        btn.textContent = '导航';
                        let menu_root_div = rander_tree(res_menu.data);
                        document.body.insertBefore(menu_root_div, document.body.firstElementChild);
                        menu_root_div.classList.add('hide');
                        btn.addEventListener('click', function () {
                            menu_root_div.classList.toggle('hide');
                            kl.id('w0').parentElement.classList.toggle('body_content_div');
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
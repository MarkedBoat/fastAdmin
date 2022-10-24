let lazyloader = function (input_opts) {

    let handle_this = {
        is_init: false,
        init: function () {
            //尝试创建 modal 之类的
            console.log('惰加载 init 尚未注册');
        },
        show: function () {
            //展示 modal
            console.log('惰加载 show 尚未注册');
        },
        '__doc': {
            btns: '[],插入内容',
        },
    };
    let scripts = document.getElementsByTagName('script');
    console.log(scripts);
    let base_url = '';
    let tmp_len = scripts.length;
    let exist_srcs = [];
    for (let i = 0; i < tmp_len; i++) {
        console.log(i);
        exist_srcs.push(scripts[i].src.replace(/^http(.*)?\/static\/tmp\w+\/js\/(.+\.js)(.*)$/g, '$2'));
    }

    console.log('exist_srcs', exist_srcs);
//"/static/js/hammer/kl-hammer.js"
    let all_srcs = [
        //  '/static/js/hammer/kl-hammer.js',
        'hammer-yii2/bootstrap.min.20211020.1.js',
        'string/string.js',
        'qrcode.js'
    ];
    let new_scripts = [];
    all_srcs.forEach(function (src) {
        if (exist_srcs.indexOf(src) === -1) {
            let new_script = new Emt('script', ('src="/static/tmp/js/' + src + '"'), '', {is_loaded: false});
            new_script.addEventListener('load', function () {
                console.log('lazy load', src);
                this.is_loaded = true;
                handle_this.init();
            });
            new_scripts.push(new_script);
            document.body.appendChild(new_script);
        }
    });
    if (!kl.id('lazyloader_style')) {
        document.body.appendChild(new Emt('style').setPros({
            innerHTML: '' +
                '.lazyloder_box{} ' +
                '.lazyloder_box>.lazyloder_btns{float:left;width:100%;} ' +
                '.lazyloder_btns>.btn{margin:10px;}' +
                ''
        }));
    }

    handle_this.init = function () {
        if (handle_this.is_init === false) {
            let tmp_is_all_loaded = true;
            new_scripts.forEach(function (new_script) {
                if (new_script.is_loaded === false) {
                    tmp_is_all_loaded = false;
                    console.log('惰加载 尚未完毕', new_script);
                }
            });
            if (tmp_is_all_loaded === false) {
                console.log('惰加载 尚未完毕 返回');
                return false;
            }
            handle_this.is_init = true;
        }


        let hammerYii2BootstarpLazyload = hammerYii2Bootstarp();
        let more_action_modal_handle = hammerYii2BootstarpLazyload.modal({title: '更多操作'});


        let qrcode_modal_handle = hammerYii2BootstarpLazyload.modal({title: '页面分享'});
        let qrcode_div = new Emt('div');
        let qrcode_text = new Emt('p');
        qrcode_modal_handle.body_ele.addNodes([
            qrcode_div,
            qrcode_text
        ]);
        let qrcode = new QRCode(qrcode_div, {
            width: 200,//设置宽高
            height: 200
        });


        let qrcode_btn_handle = hammerYii2BootstarpLazyload.button({title: '生成页面二维码'}).opts.color.set('info').opts.size.set('sm');
        qrcode_btn_handle.root_ele.addEventListener('click', function () {
            qrcode_modal_handle.show();
            qrcode.makeCode(document.location.href);
            qrcode_text.textContent = document.location.href;
        });

        more_action_modal_handle.body_ele.setPros({className: 'lazyloder_box'}).addNodes([
            new Emt('div', 'class="lazyloder_btns"').addNodes([
                qrcode_btn_handle.root_ele
            ])
        ]);
        if (!(kl.isUndefined(serverData, 'user.id') === false && serverData.user.id)) {
            more_action_modal_handle.body_ele.addNodes([
                new Emt('div', 'class="lazyloder_btns"').addNodes([
                    hammerYii2BootstarpLazyload.button({
                        tagName: 'a', title: '#'
                    }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({textContent: '登录', href: '/log'}),
                ])

            ]);
        }


        if (kl.isUndefined(serverData, 'user.role_codes') === false && typeof serverData.user.role_codes.forEach === 'function') {
            if (serverData.user.role_codes.indexOf('mark_adder') !== -1) {
                more_action_modal_handle.body_ele.addNodes([
                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a', title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: '添加文章',
                            href: 'http://back.markedboat.com/mark/add20211021'
                        }),
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a', title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({textContent: '查看草稿箱', href: '/imark/draft'}),
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a', title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({textContent: '集合', href: '/imark/collections'}),
                    ])
                ])
            }
            if (serverData.user.role_codes.indexOf('admin') !== -1) {
                more_action_modal_handle.body_ele.addNodes([
                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('danger').opts.size.set('sm').root_ele.setPros({
                            textContent: '后台', href: 'http://back.markedboat.com'
                        }),
                    ]),
                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: '提醒', href: 'http://back.markedboat.com/notify/list'
                        }),
                    ]),
                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: 'add只言片语',
                            href: 'https://markedboat.com/shortmsg/add'
                        }),
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: '查看只言片语',
                            href: 'https://markedboat.com/shortmsg/list'
                        }),
                    ]),
                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({textContent: '小说', href: '/novel/novels'}),
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({textContent: '合并任务', href: '/novel/list_task'}),
                        hammerYii2BootstarpLazyload.button({
                            tagName: 'a',
                            title: '#'
                        }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: '我的文件',
                            href: 'https://markedboat.com/upload/my_files'
                        })
                    ]),

                ])
            }

            if (serverData.user.role_codes.indexOf('backend_admin') !== -1) {

                let tmp_list = function (obj) {
                    console.log(obj);
                    let link = '';
                    if (obj.is_backend === 1) {
                        link = 'http://back.markedboat.com' + obj.url;//后台
                    } else if (obj.is_backend === 2) {
                        link = obj.url;//前台的
                    } else {
                        link = obj.url;//外网链接
                    }
                    let li = new Emt('li').addNodes([
                        hammerYii2BootstarpLazyload.button({tagName: 'a',})
                            .opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                            textContent: obj.title, href: link
                        })
                    ]);
                    if (obj.subs.length > 0) {
                        let ul = new Emt('ul');
                        obj.subs.forEach(function (sub_info) {
                            ul.addNodes([tmp_list(sub_info)]);
                        });
                        li.addNode(ul);
                    }
                    return li;
                };

                kl.ajax({
                    url: '/rbac/menus',
                    data: {},
                    method: 'GET',
                    success: function (res_menu) {
                        console.log(res_menu);

                        if (res_menu.status) {
                            if (res_menu.status === 200) {
                                more_action_modal_handle.body_ele.addNodes([
                                    new Emt('div', 'class="lazyloder_btns"').addNodes([
                                        function (ul) {
                                            res_menu.data.tree.forEach(function (tree) {
                                                ul.addNode(tmp_list(tree));
                                            });
                                            return ul;
                                        }(new Emt('ul'))
                                    ])
                                ]);
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


            }

        }


        more_action_modal_handle.body_ele.addNodes([
            new Emt('div', 'class="lazyloder_btns"').addNodes([
                hammerYii2BootstarpLazyload.button({
                    tagName: 'a',
                    title: '#'
                }).opts.color.set('danger').opts.size.set('sm').root_ele.setPros({
                    textContent: '搜索', href: 'https://markedboat.com/search/index'
                })
            ])
        ]);

        if (serverData && serverData.mark && serverData.mark.author && serverData.user_id === parseInt(serverData.mark.author)) {
            let share_btn = hammerYii2BootstarpLazyload.button({title: '分享文章7天'}).opts.color.set('info').opts.size.set('sm').root_ele;
            more_action_modal_handle.body_ele.addNodes([
                new Emt('div', 'class="lazyloder_btns"').addNodes([
                    hammerYii2BootstarpLazyload.button({
                        tagName: 'a',
                        title: '#'
                    }).opts.color.set('info').opts.size.set('sm').root_ele.setPros({
                        textContent: '修改',
                        href: 'http://back.markedboat.com/mark/edit20211021?mark_id=' + serverData.mark.id
                    }),
                    share_btn
                ]),

            ]);
            share_btn.addEventListener('click', function () {
                kl.ajax({
                    url: '/imark/share7day',
                    data: {
                        mark_id: serverData.mark.id, '_csrf-frontend': serverData.csrf,
                    },
                    success: function (res_share) {
                        console.log(res_share);

                        if (res_share.status) {
                            if (res_share.status === 200) {
                                qrcode_modal_handle.show();
                                qrcode.makeCode(res_share.data.url);
                                qrcode_text.textContent = res_share.data.url + ' 过期时间为:' + res_share.data.expires;

                            } else {
                                alert('失败:' + (res_share.msg || '未知'))
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
                })
            });
        }


        handle_this.show = function () {
            more_action_modal_handle.show();
        };
        handle_this.show();

    };
    handle_this.init();

    return handle_this;

};


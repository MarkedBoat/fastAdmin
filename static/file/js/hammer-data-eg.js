    domLoaded(function () {
        //  var box = new Emt('div');
        //  box.setStyle({width: '100%', height: '100%', minHeight: '600px', minWidth: '600px'});
        let obj = {a: {b: {c: {d: 'e'}}}};
        console.log(kl.getObjValByPath(obj, 'a.b.c'));
        console.log(kl.setValByPath(obj, 'a.b.c', 'xxxxx'));
        console.log(obj);


        let show_div = new Emt('div').setPros({className: 'trace_block'});
        kl.id('json_div').append(show_div);

        let userOrderInfo = function (user_ids, pkg_fun) {
            kl.ajax({
                url: '/dbdata/query?class_name=pay/OldOrder&user_id__in=' + JSON.stringify(user_ids) + '&_size=10000',
                method: 'GET',
                type: 'json',
                success: function (res) {
                    pkg_fun({status: true, res: res.data.data_rows});
                },
                error: function (error_res) {
                    pkg_fun({status: false, res: 'retry'});
                }
            });
        };


        let tmp = {
            "1221": {
                "user_id": 1221, "nickname": "\u738b\u7eac", "egg_amount": 6978, "subs": {
                    "1230": {
                        "user_id": 1230, "nickname": "\u5f6a\u608d\u7684\u4eba\u751f\u4ece\u6765\u4e0d\u9700\u8981\u89e3\u91ca", "egg_amount": -29, "subs": {
                            "1316": {
                                "user_id": 1316,
                                "nickname": "\u61a8\u9017\u90ce\u5148\u751f",
                                "egg_amount": 0,
                                "subs": {
                                    "1649": {"user_id": 1649, "nickname": "\u6211\u662f\u674e\u6587\u529f", "egg_amount": 0, "subs": []},
                                    "1650": {"user_id": 1650, "nickname": "\u61a8\u9017\u90ce~\u534e\u51ef\u5f71\u89c6", "egg_amount": 0, "subs": []}
                                }
                            },
                            "1983": {"user_id": 1983, "nickname": "\u81f4 \u8fdc", "egg_amount": -38, "subs": {"1984": {"user_id": 1984, "nickname": "\u6de1\u5b9a\u4eba\u751f", "egg_amount": 0, "subs": []}}},
                            "2377": {"user_id": 2377, "nickname": "\u4ed9\u798f\u5802", "egg_amount": 0, "subs": []},
                            "2371": {"user_id": 2371, "nickname": "\u7ea2\u8c46", "egg_amount": 0, "subs": []},
                            "2821": {"user_id": 2821, "nickname": "", "egg_amount": 0, "subs": []},
                            "2916": {"user_id": 2916, "nickname": "\u6c90\u5c0f\u5b89", "egg_amount": 0, "subs": {"2738": {"user_id": 2738, "nickname": "\u1770\uaadd\uaa89\uaaaf\uaac0", "egg_amount": 0, "subs": []}}},
                            "2688": {"user_id": 2688, "nickname": "Lola\u5c0f\u5446\u55b5", "egg_amount": -22, "subs": {"3105": {"user_id": 3105, "nickname": "Maryy\u5c0f\u732a", "egg_amount": 0, "subs": []}}}
                        }
                    },
                    "1236": {
                        "user_id": 1236,
                        "nickname": "\u8bb8\u591a\u4ebf.",
                        "egg_amount": -22,
                        "subs": {
                            "1605": {"user_id": 1605, "nickname": null, "egg_amount": 0, "subs": []},
                            "1606": {"user_id": 1606, "nickname": "\u6b66\u5f69\u738915655810076", "egg_amount": -34, "subs": []},
                            "1670": {
                                "user_id": 1670,
                                "nickname": "\u51b7\u5251\u9752\u950b",
                                "egg_amount": -9,
                                "subs": {
                                    "1732": {"user_id": 1732, "nickname": "\u0e08\u0e38\u0e4a\u0e1a\u82f1\u5b50", "egg_amount": -15, "subs": []},
                                    "2059": {"user_id": 2059, "nickname": "\u9093\u6210\u9f9915118081030", "egg_amount": 0, "subs": []},
                                    "2555": {"user_id": 2555, "nickname": "\u6d45\u6d45", "egg_amount": -10, "subs": []}
                                }
                            }
                        }
                    },
                    "1239": {
                        "user_id": 1239, "nickname": "\u6dee\u5317", "egg_amount": -663, "subs": {
                            "1300": {"user_id": 1300, "nickname": "\u91d1\u91d1\u91d1", "egg_amount": -31, "subs": []},
                            "1304": {"user_id": 1304, "nickname": "\u7f57\u82f1\u8d85\uff08\u660c\u8fd0\u7269\u6d41\uff09", "egg_amount": -436, "subs": []},
                            "1305": {
                                "user_id": 1305, "nickname": "Miller", "egg_amount": -67, "subs": {
                                    "1318": {
                                        "user_id": 1318, "nickname": "\u5deb\u5c0f\u6797", "egg_amount": -7078, "subs": {
                                            "1328": {"user_id": 1328, "nickname": "Priya", "egg_amount": -756, "subs": []},
                                            "1403": {"user_id": 1403, "nickname": "\u5c0f\u5f20", "egg_amount": 0, "subs": []},
                                            "1407": {
                                                "user_id": 1407, "nickname": "\u95ee\u9898\u4e0d\u5927\uff01", "egg_amount": -31, "subs": {
                                                    "1410": {"user_id": 1410, "nickname": "YS", "egg_amount": 0, "subs": []},
                                                    "1409": {"user_id": 1409, "nickname": "\u5c0f_\u3002\u718a\u732b^", "egg_amount": 0, "subs": []},
                                                    "1421": {"user_id": 1421, "nickname": "\u963f\u767d", "egg_amount": 0, "subs": []},
                                                    "1422": {"user_id": 1422, "nickname": "\u7761\u4e00\u89c9\u5c31\u597d\u4e86", "egg_amount": 0, "subs": []},
                                                    "1423": {"user_id": 1423, "nickname": "\u9ec4\u5148\u751f", "egg_amount": 0, "subs": []},
                                                    "1428": {"user_id": 1428, "nickname": "\u5c0f\u866b\u5b50\uff5e\uff5e", "egg_amount": 0, "subs": []},
                                                    "1426": {"user_id": 1426, "nickname": "\u81ea\u7136\u9192", "egg_amount": 0, "subs": []},
                                                    "1432": {"user_id": 1432, "nickname": "\u963f\u5f1f\u4ed4\u674e\u6000\u5f3a\u3002", "egg_amount": 0, "subs": []},
                                                    "1433": {"user_id": 1433, "nickname": "\u4e01\u6167", "egg_amount": 0, "subs": []},
                                                    "1434": {"user_id": 1434, "nickname": "\u4e2b\u4e2b", "egg_amount": 0, "subs": []},
                                                    "1436": {"user_id": 1436, "nickname": "fighting", "egg_amount": 0, "subs": []},
                                                    "1437": {"user_id": 1437, "nickname": null, "egg_amount": 0, "subs": []},
                                                    "1466": {"user_id": 1466, "nickname": "\u53d1\u6325\u597d\u81ea\u5df1", "egg_amount": 0, "subs": []},
                                                    "1469": {"user_id": 1469, "nickname": "1836369996", "egg_amount": 0, "subs": []},
                                                    "1473": {"user_id": 1473, "nickname": "hyqhyq1000211", "egg_amount": 0, "subs": []}
                                                }
                                            },
                                            "1408": {"user_id": 1408, "nickname": "\u884c\u5343\u91cc\uff0c\u81f4\u5e7f\u5927", "egg_amount": 0, "subs": []},
                                            "1283": {"user_id": 1283, "nickname": "\u6212\u9152\u8005\uff08\u6df1\u7231\u65e0\u58f0\uff09", "egg_amount": -136, "subs": []},
                                            "1425": {"user_id": 1425, "nickname": "a\u91cd\u6176_DNA\u9e2d\u738b\u963f\u5218", "egg_amount": -7, "subs": {"1427": {"user_id": 1427, "nickname": "L", "egg_amount": 0, "subs": []}}},
                                            "1462": {"user_id": 1462, "nickname": "Serendipity", "egg_amount": -34, "subs": []},
                                            "1461": {"user_id": 1461, "nickname": "\u9752\u5c71\u5e38\u5728", "egg_amount": -396, "subs": []},
                                            "1499": {"user_id": 1499, "nickname": "\u5c0f\u82b8", "egg_amount": -542, "subs": {"1521": {"user_id": 1521, "nickname": "\u71d5\u5b50", "egg_amount": -251, "subs": []}}},
                                            "1734": {"user_id": 1734, "nickname": " ", "egg_amount": -552, "subs": []},
                                            "2112": {"user_id": 2112, "nickname": "Minnie.", "egg_amount": -501, "subs": []},
                                            "2133": {"user_id": 2133, "nickname": "\u79ef\u5584\u884c", "egg_amount": -387, "subs": []}
                                        }
                                    },
                                    "1243": {"user_id": 1243, "nickname": "\u989c\u6d77", "egg_amount": -34, "subs": {"3241": {"user_id": 3241, "nickname": "\u6d2a\u5f6c", "egg_amount": 0, "subs": []}}},
                                    "1330": {"user_id": 1330, "nickname": null, "egg_amount": 0, "subs": []}
                                }
                            },
                            "1703": {"user_id": 1703, "nickname": "\u6267\u7740", "egg_amount": 0, "subs": []},
                            "1830": {"user_id": 1830, "nickname": "\u5979\u96c5\u808c\u80a4\u4f18\u5316 \u5168\u7403\u8d2d", "egg_amount": -18, "subs": []}
                        }
                    },
                    "1317": {
                        "user_id": 1317,
                        "nickname": "\u6b66\u91d1\u661f",
                        "egg_amount": -369,
                        "subs": {
                            "2994": {"user_id": 2994, "nickname": "\u6b66\u6587", "egg_amount": -1008, "subs": {"3213": {"user_id": 3213, "nickname": "\u5b81\u548c\u99a8", "egg_amount": -751, "subs": []}}},
                            "3163": {"user_id": 3163, "nickname": "\u83ab\u540d ", "egg_amount": 40, "subs": []},
                            "3238": {"user_id": 3238, "nickname": "party  of  five", "egg_amount": 85, "subs": {"3339": {"user_id": 3339, "nickname": "\u5b89\u5f92\u751f\u4e22\u4e86\u7ae5\u8bdd", "egg_amount": -6, "subs": []}}},
                            "3301": {"user_id": 3301, "nickname": "zj", "egg_amount": 0, "subs": []},
                            "3725": {"user_id": 3725, "nickname": "\u6b66\u91d1\u6d9b", "egg_amount": 0, "subs": []},
                            "4062": {"user_id": 4062, "nickname": "\u5218\u5fcd", "egg_amount": 0, "subs": []}
                        }
                    },
                    "1273": {"user_id": 1273, "nickname": "\u6210\u90fd\u836f\u6750\u5e02\u573a\uff5e\u5218\u677e", "egg_amount": -16, "subs": {"1506": {"user_id": 1506, "nickname": "\u96f7\u96e8", "egg_amount": 0, "subs": []}}},
                    "1438": {"user_id": 1438, "nickname": "\u56fd\u5f3a\u6c11\u5bcc", "egg_amount": -303, "subs": {"1440": {"user_id": 1440, "nickname": "\u51b0\u98ce", "egg_amount": 1545, "subs": []}}},
                    "1441": {"user_id": 1441, "nickname": "\u5584\u7f18", "egg_amount": 0, "subs": []},
                    "1346": {
                        "user_id": 1346, "nickname": "\u5f20\u534e-\u7231\u4e4b\u604b\u7f51\u7edc\u79d1\u6280", "egg_amount": -886, "subs": {
                            "1638": {
                                "user_id": 1638, "nickname": "\u5d14\u6d77\u6d0b\u3010\u4e00\u8d77\u6765\u517b\u8702\u3011", "egg_amount": 0, "subs": {
                                    "1689": {
                                        "user_id": 1689, "nickname": "\u5d14\u5927\u6d77\u3010\u4e00\u8d77\u6765\u517b\u8702\u3011", "egg_amount": 0, "subs": {
                                            "1698": {
                                                "user_id": 1698, "nickname": "\u827a\u6d0b", "egg_amount": -3901, "subs": {
                                                    "1701": {"user_id": 1701, "nickname": "\u4e91\u6de1\u98ce\u8f7b\u8fd1\u5348\u5929", "egg_amount": 2, "subs": []},
                                                    "2261": {"user_id": 2261, "nickname": "\u674e\u4fca\u5cf0", "egg_amount": 0, "subs": []},
                                                    "2420": {"user_id": 2420, "nickname": "\u5b64\u50b2", "egg_amount": 0, "subs": []},
                                                    "2421": {"user_id": 2421, "nickname": "dir", "egg_amount": 0, "subs": []},
                                                    "2245": {
                                                        "user_id": 2245,
                                                        "nickname": "\u56e1\u56e1",
                                                        "egg_amount": 0,
                                                        "subs": {"2674": {"user_id": 2674, "nickname": "\u8702", "egg_amount": 0, "subs": {"2675": {"user_id": 2675, "nickname": "\u559c\u6d0b\u6d0b", "egg_amount": 0, "subs": []}}}}
                                                    },
                                                    "2649": {"user_id": 2649, "nickname": "\u9648\u5927\u5927", "egg_amount": 0, "subs": []},
                                                    "2721": {"user_id": 2721, "nickname": "\u66fc\u8389", "egg_amount": 0, "subs": []},
                                                    "2722": {"user_id": 2722, "nickname": "\u6de1\u96c5\u91ca\u7136", "egg_amount": 0, "subs": []},
                                                    "2723": {"user_id": 2723, "nickname": "\u666e\u62c9\u8fbe\u6ca1\u6709\u5973\u738b_", "egg_amount": 0, "subs": []},
                                                    "2724": {"user_id": 2724, "nickname": "Mi Manchi", "egg_amount": 0, "subs": []},
                                                    "2788": {"user_id": 2788, "nickname": "~~\u59b3\u904e\u4f86\u5416~~", "egg_amount": 0, "subs": []},
                                                    "2654": {"user_id": 2654, "nickname": "\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0", "egg_amount": 0, "subs": []},
                                                    "2808": {"user_id": 2808, "nickname": "\u8fce\u5ba2\u677e", "egg_amount": 0, "subs": []},
                                                    "2809": {"user_id": 2809, "nickname": "\u6f47\u510a\u58a8", "egg_amount": 0, "subs": []},
                                                    "2810": {"user_id": 2810, "nickname": "\u6052\u54e5v", "egg_amount": 0, "subs": []},
                                                    "2572": {"user_id": 2572, "nickname": ".", "egg_amount": -8, "subs": []},
                                                    "2812": {"user_id": 2812, "nickname": "\u963fQ", "egg_amount": 0, "subs": []},
                                                    "2814": {"user_id": 2814, "nickname": "\u5c0f\u9f99\u732b", "egg_amount": 0, "subs": []},
                                                    "2990": {"user_id": 2990, "nickname": "@MR.Leo   \u901a \ue003 \u900f", "egg_amount": 0, "subs": []},
                                                    "2991": {"user_id": 2991, "nickname": "\u5b81\u9759\u81f4\u8fdc", "egg_amount": 0, "subs": []},
                                                    "2997": {"user_id": 2997, "nickname": "Xiyun", "egg_amount": 0, "subs": {"2998": {"user_id": 2998, "nickname": null, "egg_amount": 0, "subs": []}}},
                                                    "2999": {"user_id": 2999, "nickname": "\u55ef", "egg_amount": 0, "subs": []},
                                                    "2728": {"user_id": 2728, "nickname": "\u6768", "egg_amount": 0, "subs": []},
                                                    "3088": {"user_id": 3088, "nickname": "\u52aa\u529b\u62fc\u640f", "egg_amount": 0, "subs": []},
                                                    "3085": {"user_id": 3085, "nickname": "\u3001\u4fe1\u9a6c\u7531\u7f30", "egg_amount": 0, "subs": []},
                                                    "3165": {"user_id": 3165, "nickname": "\u67ef", "egg_amount": 0, "subs": []},
                                                    "3223": {"user_id": 3223, "nickname": "\u55b5", "egg_amount": 0, "subs": []},
                                                    "3274": {"user_id": 3274, "nickname": "\u4fca\u7389\u8431", "egg_amount": 0, "subs": []},
                                                    "3277": {"user_id": 3277, "nickname": "zao", "egg_amount": 0, "subs": []},
                                                    "3287": {"user_id": 3287, "nickname": "\u6881\u4e16\u5c55", "egg_amount": 0, "subs": []},
                                                    "3294": {"user_id": 3294, "nickname": "ceil", "egg_amount": 0, "subs": []},
                                                    "3304": {"user_id": 3304, "nickname": "\u10e6\u1b44\ua9bf\u2112\u2134\u0475\u212f\u6155\u5ddd\u098f\u2075\u00b2\u00ba", "egg_amount": 0, "subs": []},
                                                    "2844": {"user_id": 2844, "nickname": "A      \u5d14\u5f6c", "egg_amount": 0, "subs": []},
                                                    "3578": {"user_id": 3578, "nickname": "1", "egg_amount": -232, "subs": []},
                                                    "3641": {"user_id": 3641, "nickname": "\u5c0f\u60a6", "egg_amount": 0, "subs": []},
                                                    "3751": {"user_id": 3751, "nickname": "\u5c0f\u8725\u8734", "egg_amount": 0, "subs": []},
                                                    "3889": {"user_id": 3889, "nickname": "X-bao", "egg_amount": 0, "subs": []}
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            "1348": {"user_id": 1348, "nickname": "Melancholy", "egg_amount": 0, "subs": []},
                            "1770": {"user_id": 1770, "nickname": "\u9e3f\u7f8e", "egg_amount": 0, "subs": []},
                            "2394": {"user_id": 2394, "nickname": "\u76c8\u76c8\u6211\u6240\u601d", "egg_amount": 0, "subs": []},
                            "3340": {"user_id": 3340, "nickname": "\u83ab\u975e", "egg_amount": 0, "subs": []},
                            "3342": {"user_id": 3342, "nickname": "\u56e2\u961f", "egg_amount": 0, "subs": []},
                            "3349": {"user_id": 3349, "nickname": "\u5996\u5b7d\u767d\u3001\u3065 Yoon A", "egg_amount": 0, "subs": []},
                            "3432": {"user_id": 3432, "nickname": "\u66fe\u9732\u8317", "egg_amount": 0, "subs": []},
                            "3520": {"user_id": 3520, "nickname": "\u5e8f\u7ae0", "egg_amount": 0, "subs": []},
                            "3541": {"user_id": 3541, "nickname": "\u4e9a\u7279\u5170\u8482\u65af", "egg_amount": 0, "subs": []},
                            "3672": {"user_id": 3672, "nickname": "\u5806\u79ef\u6728", "egg_amount": 0, "subs": []},
                            "3999": {"user_id": 3999, "nickname": "\u534e\u76db\u7f8e\u4e1a\u00ae\u5f6d\u5eb7", "egg_amount": 0, "subs": []}
                        }
                    },
                    "1612": {"user_id": 1612, "nickname": "\u5218\u5c0f\u5b87", "egg_amount": 0, "subs": []},
                    "1664": {"user_id": 1664, "nickname": "\u8ecd\u5cf0", "egg_amount": 0, "subs": []},
                    "1226": {"user_id": 1226, "nickname": "\u79b9\u67ab", "egg_amount": 0, "subs": {"1709": {"user_id": 1709, "nickname": "\u6bcf\u597d\u7684\u4e00\u5929", "egg_amount": 0, "subs": []}}},
                    "1622": {"user_id": 1622, "nickname": "\u4e00\u7b11\u800c\u8fc7", "egg_amount": -7000, "subs": {"1887": {"user_id": 1887, "nickname": "QA", "egg_amount": 0, "subs": []}}},
                    "1276": {"user_id": 1276, "nickname": "\u9f0e\u5143\u5218\u6653\u5a1f_18096769600", "egg_amount": 0, "subs": []},
                    "1729": {"user_id": 1729, "nickname": "\u8a3e\u632f", "egg_amount": 0, "subs": []},
                    "1787": {"user_id": 1787, "nickname": "\u738b\u6653\u4e3d", "egg_amount": 0, "subs": []},
                    "1861": {"user_id": 1861, "nickname": "A_DD", "egg_amount": 0, "subs": []},
                    "2449": {"user_id": 2449, "nickname": "\u963f\u9896\uff5c\u88c5\u4fee\u8bbe\u8ba1\u3001\u4e8c\u624b\u8f66\u4ea4\u6613", "egg_amount": 0, "subs": []}
                }
            }
        };

        let getTreeSrcRows = function (res_list, input_data, name_root) {
            if (typeof input_data.subs === 'object') {
                for (let sub_k in input_data.subs) {
                    let input_data2 = input_data.subs[sub_k];
                    if (input_data2.subs.length === 0) {
                        res_list.push({path: name_root + '.subs.' + sub_k, val: input_data2, user_id: input_data2.user_id});
                    } else {
                        getTreeSrcRows(res_list, input_data2, 1, name_root + '.subs.' + sub_k);
                    }
                }
            } else {
                for (let user_id in input_data) {
                    res_list.push({path: name_root + (name_root.length > 0 ? '.' : '') + user_id, val: input_data[user_id], user_id: user_id});
                    getTreeSrcRows(res_list, input_data[user_id].subs, name_root + (name_root.length > 0 ? '.' : '') + user_id + '.subs');
                }
            }
        };

        // show_div.appendChild(view_json(tmp));
        // return false;
        kl.ajax({
            url: '/inviter/treeData?user_id={$user_id}',
            method: 'GET',
            type: 'json',
            success: function (res) {

                let src_rows = [];
                getTreeSrcRows(src_rows, res, '');
                console.log(src_rows);

                kl.packageDataInBatch({
                    srcRows: src_rows,
                    srcKey: 'user_id',
                    resKey: 'uid',
                    queryResFun: function (user_ids, pkg_fun) {
                        userOrderInfo(user_ids, pkg_fun);
                    },
                    onSuccess: function (info) {
                        console.log('onSuccess', info);
                        show_div.appendChild(view_json(res));
                    },
                    onResMatch: function (srcRowInfo, resInfo) {
                        console.log('onResMatch', 'srcInfo:', srcRowInfo, 'orders:', kl.getObjValByPath(res, srcRowInfo.path + '.orders'), 'resInfo:', resInfo);
                        let tmp_egg_amount = 0;
                        if (kl.getObjValByPath(res, srcRowInfo.path + '.payed_egg_amount') === undefined) {
                            kl.setValByPath(res, srcRowInfo.path + '.payed_egg_amount', 0);
                        } else {
                            tmp_egg_amount = parseInt(kl.getObjValByPath(res, srcRowInfo.path + '.payed_egg_amount'));
                        }
                        if (kl.getObjValByPath(res, srcRowInfo.path + '.orders') === undefined) {
                            // kl.setValByPath(res, srcRowInfo.path + '.orders', []);
                        }

                        tmp_egg_amount += parseInt(resInfo.egg);
                        kl.setValByPath(res, srcRowInfo.path + '.payed_egg_amount', tmp_egg_amount);

                        //   kl.setValByPath(res, srcRowInfo.path + '.orders', resInfo, true);

                    },
                    onResEmpty: function (srcRowInfo) {
                        console.log('onResEmpty', srcRowInfo);
                        kl.setValByPath(res, srcRowInfo.path + '.payed_egg_amount', 0);
                        //kl.setValByPath(res, srcRowInfo.path + '.orders', []);
                    },
                });
                src_rows.forEach(function (info) {
                    // kl.setValByPath(res, info.path + '.xxx', 'path->' + info.path);
                });

                // console.log(res);
                //  show_div.appendChild(view_json(res));

            },
            error: function (error_res) {

            }
        });


    });

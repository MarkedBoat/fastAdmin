top.window.captureInfo = top.window.captureInfo || {
    music163: {
        lyric: false,
        audio_link: false,
    },
};

//为了解决 注入脚本之前 的ajax 带有关键信息，所以写一些到 localStorage 里
let clear_lock = false;
setInterval(function () {
    if (clear_lock === false) {
        clear_lock = true;
        if (top.window.captureInfo.music163.lyric !== false && top.window.captureInfo.music163.audio_link !== false) {
            XMLHttpRequest.prototype._record = false;//停止写，但是不能停止删除
        }
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i); //获取本地存储的Key
            if (key.indexOf('js_request_') === 0) {
                let tmp_ar = localStorage.getItem(key).split('[kiwi_js]');
                if (tmp_ar.length === 2) {
                    if (tmp_ar[0].indexOf('https://interface.music.163.com/weapi/song/lyric?csrf_token') === 0) {
                        try {
                            console.log("\n----------------------------------\nlyirc_info\n", tmp_ar[1],);
                            let lyrc_info = JSON.parse(tmp_ar[1]);
                            window.captureInfo.music163.lyric = lyrc_info.lrc.lyric;
                            console.log('lyirc_info', lyrc_info, lyrc_info.lrc.lyric);
                        } catch (e) {
                            alert('歌词搞不出来了' + e.message);
                        }
                    } else if (tmp_ar[0].indexOf('https://interface.music.163.com/weapi/song/enhance/player/url/v1?csrf_token') === 0) {
                        try {
                            console.log("\n----------------------------------\naudio_link_info\n", tmp_ar[1],);
                            let audio_info = JSON.parse(tmp_ar[1]);
                            window.captureInfo.music163.audio_link = audio_info.data[0].url;
                            console.log('audio_link_info', audio_info, audio_info.data[0].url);
                        } catch (e) {
                            alert('音乐播放链接搞不出来了' + e.message);
                        }
                    }
                }
                console.log(tmp_ar[0], key);
                localStorage.removeItem(key);

            }
        }
    }
    clear_lock = false;
}, 100);


(function () {
        console.log('_delay_lock 解锁');
        XMLHttpRequest.prototype._delay_lock = false;
        if (top.document.getElementById('kl_kiwi_menu_root')) {
            return false;
        }
        let root_div = new Emt('div', 'id="kl_kiwi_menu_root"');
        top.document.body.append(root_div);
        let currentInfo = {
            table: false,
            x: 0,
            y: 0,
            keep_hide: false,
        };


        let BoxCellButton = function () {
            let self = new Emt('button', 'type="button"');
            self.init_data = {text: 'button_text', link: '####', sourceData: {}};
            self.isGridElement = true;

            self.loadData = function (init_data) {
                self.init_data = init_data;
                return self;
            };
            self.reDrawElement = function () {
                self.textContent = self.init_data.text;
                return self;
            };
            self.play = function () {
                if (self.init_data.type === 'link') {
                    window.location.href = self.init_data.link;
                } else if (self.init_data.type === 'fun') {
                    self.init_data.fun();
                } else {
                    alert(self.init_data.handleKey);
                }
            };
            return self;
        };

        let menu_map_top_btns = ([
            {text: 'HOME', handleKey: 'home', type: 'link', link: 'https://markedboat.com/cors/kiwi'},

            {
                text: '暂时隐藏窗口', handleKey: 'test', type: 'fun', fun: function () {
                    root_div.classList.add('hide');
                }
            },
            {
                text: '长效隐藏窗口', handleKey: 'test', type: 'fun', fun: function () {
                    root_div.classList.add('hide');
                    currentInfo.keep_hide = true;
                }
            },
            {
                text: '关闭页面', handleKey: 'test', type: 'fun', fun: function () {
                    // window.close();
                    window.localStorage.setItem('close_current_window', 'true');
                }
            },

        ]).map(function (cfg) {
            return (new BoxCellButton().loadData(cfg));
        });


        let jumping_status = false;


        let BoxTr = function (box_table, input_param) {
            let init_opts = input_param || {x: 1, y: 1};
            let self = box_table.insertRow();
            return self;
        };
        let BoxCell = function (box_tr, input_param) {
            let init_opts = input_param || {td_x: 2, td_y: 1, sourceData: {}};
            let self = box_tr.insertCell();
            self.td_x = input_param.td_x;
            self.td_y = input_param.td_y;
            self.is_close = true;
            self.boxInfo = init_opts;
            self.girdObject = false;
            self.loadCellObject = function (gridObject) {
                self.girdObject = gridObject;
                return self;
            };
            self.initCell = function () {
                self.is_close = false;
                self.girdObject.reDrawElement();
                self.girdObject.gridCell = self;
                self.append(self.girdObject);
                return self;
            };
            self.closeCell = function () {
                self.girdObject = false;
                self.is_close = true;
                return self;
            };
            self.addButton = function () {
                let btn = new BoxCellButton(self, input_param);
                self.append(btn);
                return self;
            };
            return self;
        };

        let BoxTable = function (input_param) {
            let self = new Emt('table');
            let init_opts = input_param || {colsNum: 2, rowsNum: 1, sourceData: [], dataRows: []};
            self.colsNum = init_opts.colsNum || 2;
            self.rowsNum = init_opts.rowsNum || 1;
            self.tdMaxX = self.colsNum - 1;
            self.tdMaxY = self.rowsNum - 1;
            self.sourceData = init_opts.sourceData || [];
            self.dataRows = init_opts.dataRows || [];
            self.pos = {x: 0, y: 0, last_td_x: 0, last_td_y: 0};
            self.current = {gridData: {maxRowIndex: 0, maxColIndex: 0}};
            self.gridCell = false;
            self.isTable = true;
            self.isGridElement = true;
            self.directions = init_opts.directions || {left: false, right: false, top: false, down: false};


            self.tds = [];
            self.tdsRowColsLength = [];
            // self.table = new Emt('table');
            self._initCells = function () {
                //生成表格
                for (let y = 0; y < self.rowsNum; y++) {
                    let tmp_tr = new BoxTr(self, {});
                    self.tds.push([]);
                    for (let x = 0; x < self.colsNum; x++) {
                        let tmp_td = new BoxCell(tmp_tr, {td_x: x, td_y: y});
                        self.tds[y].push(tmp_td);
                    }
                }
            };
            self._initCells();

            self.reDrawElement = function () {

                //生成数据 2D map二维地图,并尝试绑定在 表格上

                self.tdsRowColsLength = [];//记录可以渲染多少个


                let tmp_num = Math.ceil(self.sourceData.length / self.colsNum);
                if (self.sourceData.length) {
                    for (let y = 0; y < tmp_num; y++) {
                        let tmp_start = y * self.colsNum;
                        let tmp_array = self.sourceData.slice(tmp_start, tmp_start + self.colsNum);
                        if (self.dataRows[y] === undefined) {
                            self.dataRows.push([]);
                            self.current.gridData.maxRowIndex = y;
                        }
                        tmp_array.forEach(function (init_info, index_x) {
                            init_info.map_x = index_x;
                            init_info.map_y = y;
                            self.dataRows[y].push(init_info);
                            self.current.gridData.maxColIndex = index_x;
                        });

                        // self.dataRows.push(tmp_array);
                        //console.log(tmp_start, tmp_start + self.colsNum, self.sourceData.slice(tmp_start, tmp_start + self.colsNum));
                        //console.log('0,3',self.sourceData.slice(0, 3));//0,1,2  不含 3
                        //console.log('0,2',self.sourceData.slice(0, 2));//0,1  不含 2
                        //console.log('1,3',self.sourceData.slice(1, 3));//1,2 不含3
                    }

                    self.tds.forEach(function (tds, index_y) {
                        if (self.dataRows[index_y]) {
                            self.tdsRowColsLength.push(self.dataRows[index_y].length);
                            tds.forEach(function (td, index_x) {
                                if (self.dataRows[index_y][index_x]) {
                                    self.tds[index_y][index_x].loadCellObject(self.dataRows[index_y][index_x]).initCell();
                                } else {
                                    self.tds[index_y][index_x].closeCell();
                                }
                            });
                        } else {
                            tds.forEach(function (td, index_x) {
                                self.tds[index_y][index_x].closeCell();
                            });
                        }
                    });
                }
                return self;
            };


            self.selectCell = function (td_x, td_y, arrow) {
                currentInfo.table = self;
                currentInfo.x = td_x;
                currentInfo.y = td_y;
                console.log(
                    "\n------selectCell-------\n",
                    //   JSON.stringify(res, null, 2)
                    td_x, td_y, arrow, "\n",
                    self.tds,
                    "\n", (self.tds[td_y] === undefined || self.tds[td_y][td_x] === undefined || self.tds[td_y][td_x].is_close)
                );
                if (self.tds[td_y] === undefined || self.tds[td_y][td_x] === undefined || self.tds[td_y][td_x].is_close === true) {
                    self.outTable(td_x, td_y, arrow);
                } else {
                    if (self.tds[td_y][td_x].girdObject.isTable === true) {
                        self.tds[td_y][td_x].girdObject.intoTable(td_x, td_y, arrow);
                    } else {
                        self.tds[td_y][td_x].girdObject.focus();
                        self.pos.last_td_x = td_x;
                        self.pos.last_td_y = td_y;

                    }
                }

            };


            self.intoTable = function (src_td_x, src_td_y, arrow) {
                let dst_x = src_td_x === false ? self.pos.last_td_x : src_td_x;
                let dst_y = src_td_y === false ? self.pos.last_td_y : src_td_y;
                let max_y = self.tdsRowColsLength - 1;
                let max_x = 0;

                if (arrow === 'left') {
                    if (src_td_y > max_y) {
                        max_x = self.tdsRowColsLength[max_y];
                    } else {
                        max_x = self.tdsRowColsLength[src_td_y];
                    }
                    dst_x = max_x;
                    console.log("intoTable -> left x->max y:keep");
                } else if (arrow === 'up') {
                    dst_y = max_y;
                    max_x = self.tdsRowColsLength[max_y];
                    dst_x = max_x;

                    console.log("intoTable -> up x:keep y->max");
                } else if (arrow === 'right') {
                    if (src_td_y > max_y) {
                        max_x = self.tdsRowColsLength[max_y];
                    } else {
                        max_x = self.tdsRowColsLength[src_td_y];
                    }
                    dst_x = 0;
                    console.log("intoTable -> right x->0 y:keep");
                } else if (arrow === 'down') {
                    dst_y = 0;
                    max_x = self.tdsRowColsLength[0];
                    dst_x = max_x;
                    console.log("intoTable -> up x->keep y->0");
                } else if (arrow === 'init') {
                    dst_y = 0;
                    max_x = self.tdsRowColsLength[0];
                    dst_x = 0;
                    console.log("intoTable -> up x->0 y->0");
                } else {
                }
                console.log("\nx->src:", src_td_x, 'max:', max_x, 'dst:', dst_x, "\ny->src:", src_td_y, 'max:', max_y, 'dst:', dst_y,);
                self.selectCell(dst_x, dst_y, arrow);
            };
            self.outTable = function (dst_x, dst_y, arrow) {
                console.log(
                    "\n------outTable-------\n",
                    //   JSON.stringify(res, null, 2)
                    dst_x, dst_y, arrow, "\n",
                    'last_td_pos:', self.pos.last_td_x, self.pos.last_td_y, "\n",
                    self.tds, self.directions[arrow],
                    "\n",
                );
                if (self.directions[arrow] === false) {
                    console.log(
                        "\n------outTable -> selectCell-------\n",
                        //   JSON.stringify(res, null, 2)
                        dst_x, dst_y, arrow, "\n",
                        'last_td_pos:', self.pos.last_td_x, self.pos.last_td_y, "\n",
                        "此路不通\n",
                    );
                    self.selectCell(self.pos.last_td_x, self.pos.last_td_y, arrow);
                } else {
                    //left:{nextTable:xxx}
                    self.directions[arrow].nextTable.intoTable(false, false, arrow);
                }

            };


            self.setGridCellData = function ({index_x: index_x, index_y: index_y, info: info}) {
                let array_ele_index = index_y * self.colsNum + index_x;
                if (self.sourceData[array_ele_index] === undefined) {
                    alertMsg('请用addGridCellData');
                } else {
                    self.sourceData[array_ele_index] = info;
                }
                return self;
            };
            self.addGridCellData = function (info) {
                self.sourceData.push(info);
                return self;
            };


            self.unlock = function () {
                return jumping_status = false;
            };
            return self;

        };
        let root_boxTable = (new BoxTable({colsNum: 1, rowsNum: 5}));
        root_div.addNode(root_boxTable);
        let menu_boxTale = (new BoxTable({colsNum: 4, rowsNum: 1, sourceData: menu_map_top_btns}));
        root_boxTable.addGridCellData(menu_boxTale);


        root_boxTable.reDrawElement();

        menu_boxTale.selectCell(0, 0, 'init');


        if (1)
            document.onkeydown = function (e) {
                if (jumping_status === false) {
                    console.clear();
                    let keyCode = e.key || 'e.key';
                    let whichCode = e.which || 'e.which';
                    let charCode = e.charCode || 'e.charCode';
                    console.log(e.target);
                    // 37 l  38 top  39 right  40 down
                    if (currentInfo.keep_hide === false && [13, 37, 38, 39, 40].indexOf(whichCode) !== -1) {
                        root_div.classList.remove('hide');
                        switch (whichCode) {
                            case 13:
                                //ok
                                console.log(document.activeElement);
                                if (document.activeElement.isGridElement !== true) {
                                    currentInfo.table.selectCell(currentInfo.x, currentInfo.y, 'reset');
                                }
                                if (document.activeElement.isGridElement !== true) {
                                    alert('失去焦点了');
                                }
                                document.activeElement.play();
                                break;
                            case 37:
                                // e.target.gridTable.goNext(e.target, 'x', -1);
                                currentInfo.table.selectCell(currentInfo.x - 1, currentInfo.y, 'left');
                                break;
                            case 38:
                                currentInfo.table.selectCell(currentInfo.x, currentInfo.y - 1, 'top');
                                //  e.target.gridTable.goNext(e.target, 'y', -1);
                                break;
                            case 39:
                                currentInfo.table.selectCell(currentInfo.x + 1, currentInfo.y, 'right');
                                // e.target.gridTable.goNext(e.target, 'x', 1);
                                break;
                            case 40:
                                currentInfo.table.selectCell(currentInfo.x, currentInfo.y + 1, 'down');
                                //  e.target.gridTable.goNext(e.target, 'y', 1);
                                break;
                        }
                        // e.preventDefault();
                        // return false;
                    }

                }


            };

        if (!top.document.getElementById('kl_kiwi_menu_style'))
            top.document.body.append(new Emt('style', 'id="kl_kiwi_menu_style"', '' +
                '#kl_kiwi_menu_root{position: fixed; background: #DDD;width: 100%; height: auto; top: 0px; left: 0px; z-index: 99999; font-size: 20px;}' +
                '#kl_kiwi_menu_root button{font-size:20px;}' +
                '#kl_kiwi_menu_root button:focus{border-left:solid #000 0.3em;border-right:solid #000 1em;}' +
                '.hide{display:none;}' +
                '.kl_kiwi_play_box{}' +
                ''));


        let menu_map_top_btns2 = [
            {text: '知乎', handleKey: 'home', type: 'link', link: 'https://www.zhihu.com/'},
            {text: '网抑云', handleKey: 'pre', type: 'link', link: 'https://y.music.163.com/m/playlist?id=40781021'},
            {text: '网抑云登录', handleKey: 'pre', type: 'link', link: 'https://music.163.com/#/login'},

            {text: 'test', handleKey: 'next', type: 'btn'},
        ].map(function (cfg) {
            return (new BoxCellButton().loadData(cfg));
        });
        let menu_boxTale2 = (new BoxTable({colsNum: 2, rowsNum: 2, sourceData: menu_map_top_btns2,}));
        menu_boxTale2.directions.top = {nextTable: menu_boxTale};
        menu_boxTale.directions.down = {nextTable: menu_boxTale2};
        root_boxTable.addGridCellData(menu_boxTale2).reDrawElement();

        menu_boxTale.selectCell(0, 0, 'init');


        if (0 && document.location.href === 'https://y.music.163.com/m/login?redirect_url=https%3A%2F%2Fy.music.163.com%2Fm%2Fplaylist%3Fid%3D40781021#/phone?type=mail') {
            if (0) {
                let log_btn = kl.xpathSearch('.//button[contains(text(),"登录")]')[0];
                let touch_event = document.createEvent('Events');
                touch_event.initEvent('touchend', true, true);
                log_btn.dispatchEvent(touch_event);
            }
            currentInfo.keep_hide = true;
            root_div.classList.add('hide');

        }


        if (document.location.href === 'https://y.music.163.com/m/playlist?id=40781021') {


            let songs_table = (new BoxTable({colsNum: 1, rowsNum: 10, sourceData: menu_map_top_btns2,}));


            let tmp_audio = new Emt('audio', 'controls="controls"');
            let tmp_lyric = new Emt('pre');

            root_div.addNodes([
                new Emt('div', 'class="kl_kiwi_play_box"').addNodes([
                    tmp_lyric,
                    tmp_audio
                ])
            ]);
            window.captureInfo.music163.player = tmp_audio;


            window.load_song_si = window.setInterval(function () {
                let tmp_as = kl.xpathSearch('.//a[@class="m-sgitem"]');
                console.log('song_as', tmp_as);
                if (tmp_as.length < 10) {
                    return false;
                }
                window.clearInterval(window.load_song_si);
                let song_btns = [];
                tmp_as.forEach(function (a, a_index) {
                    let tmp_text = a.textContent;
                    let tmp_link = a.href;
                    if (a_index < 10) {
                        song_btns.push(new BoxCellButton().loadData({
                            text: tmp_text, type: 'fun', fun: function () {

                                let tmp_fir = new Emt('iframe');
                                root_div.addNode(tmp_fir);


                                window.captureInfo.music163.lyric = false;
                                window.captureInfo.music163.audio_link = false;
                                XMLHttpRequest.prototype._record = true;
                                console.log('清理 lyric audio_link');
                                setTimeout(function () {
                                    tmp_fir.src = tmp_link;
                                }, 200);

                                tmp_fir.addEventListener('load', function () {
                                    let tmp_si = window.setInterval(function () {
                                        if (window.captureInfo.music163.lyric !== false && window.captureInfo.music163.audio_link !== false) {
                                            //alert('可以播放了');
                                            //   kl.xpathSearch('.//div[contains(@class,"m-song-clickarea")]',tmp_fir.contentDocument)[0].click()
                                            window.clearInterval(tmp_si);
                                            tmp_audio.src = window.captureInfo.music163.audio_link;
                                            tmp_lyric.textContent = window.captureInfo.music163.lyric;
                                            tmp_audio.play();
                                            // kl.xpathSearch('.//div[contains(@class," m-song-clickarea m-song-clickarea2")]',tmp_fir.contentDocument)[0].click()
                                            tmp_fir.remove();

                                        }
                                    }, 100)
                                });
                            }
                        }));
                    }
                });
                songs_table.sourceData = song_btns;
                songs_table.directions.top = {nextTable: menu_boxTale2};
                menu_boxTale2.directions.down = {nextTable: songs_table};

                root_boxTable.addGridCellData(songs_table).reDrawElement();
                // songs_table.selectCell(0, 0, 'init');
            }, 1000);


        }

    }
)();

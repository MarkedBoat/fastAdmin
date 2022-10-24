/********************************************************************************************************************************************************
 *  _  _ _  _  ___ _  ___        /| |    ---.       ___ ____ _  _
 *  |\/| |  | (__  | /            | |__. ___|      /    |  | |\/|
 *  |  | |_/| ___) | \__          | |__| ___|      \__  |__| |  |
 *
 * 网易云页面逻辑处理部分
 *******************************************************************************************************************************************************/

(function () {

        if (top.window.kl.kiwiJs.captureInfo === undefined) {
            top.window.kl.kiwiJs.captureInfo = {
                music163: {
                    playlist: {isCompleted: false, songs: []},
                    lyric: false,
                    audio_link: false,
                },
            };
        } else {
            top.window.kl.log('网易云的已经初始化了，现在是iframe触发的，不用管了');
            return false;
        }
        if (top.window.kl === undefined) {
            throw 'top.window.kl not init';
        }




        /**
         * xhr 请求的结果会写到 storge 里面，这是添加数据处理的触发器
         */

        top.window.kl.kiwiJs.captureXhrRecorder.addTrigger('https://interface.music.163.com/weapi/v6/playlist/detail?csrf_token', function (info) {
            //key,url,text
            try {
                top.window.kl.log("\n----------------------------------\ncaptureXhrRecorder playlist detail\n", info.url,);
                let playlist_info = JSON.parse(info.text);
                top.window.kl.kiwiJs.captureInfo.music163.playlist.songs = [];
                let tmp_j = 0;
                playlist_info.playlist.tracks.forEach(function (info, tmp_i) {
                    tmp_j = tmp_i + 1;
                    if (1 || info.copyright === 0) {
                        top.window.kl.kiwiJs.captureInfo.music163.playlist.songs.push({
                            text: info.name + (info.alia.length > 0 ? "(" + info.alia.join(',') + ")" : ''),
                            desc: info.al.name,
                            pic: info.al.picUrl,
                            link: 'https://music.163.com/m/song?id=' + info.id,
                        });
                    }

                });
                if (tmp_j === playlist_info.playlist.tracks.length) {
                    top.window.kl.kiwiJs.captureInfo.music163.playlist.isCompleted = true;
                }
                top.window.kl.log('captureXhrRecorder playlist detail  top.window.kl.kiwiJs.captureInfo.music163.playlist', top.window.kl.kiwiJs.captureInfo.music163.playlist);
                info.close();

            } catch (e) {
                top.window.kl.kiwiJs.showMsg('列表搞不出来了' + e.message);
                top.window.kl.log('captureXhrRecorder playlist/detail   列表搞不出来了', info);
            }
        });


        top.window.kl.kiwiJs.captureXhrRecorder.addTrigger('https://interface.music.163.com/weapi/song/lyric?csrf_token', function (info) {
            //key,url,text
            try {
                top.window.kl.log("\n----------------------------------\ncaptureXhrRecorder lyirc_info\n", info.url,);
                let lyrc_info = JSON.parse(info.text);
                top.window.kl.kiwiJs.captureInfo.music163.lyric = lyrc_info.lrc.lyric;
                top.window.kl.log('captureXhrRecorder lyirc_info', lyrc_info, lyrc_info.lrc.lyric);
                if (top.window.kl.kiwiJs.captureInfo.music163.lyric !== false && top.window.kl.kiwiJs.captureInfo.music163.audio_link !== false) {
                    top.window.kl.kiwiJs.captureXhrRecorder.stopScan();

                }
            } catch (e) {
                top.window.kl.kiwiJs.showMsg('歌词搞不出来了' + e.message);
                top.window.kl.log('captureXhrRecorder lyirc_info 歌词搞不出来了', info);
            }
        });

        top.window.kl.kiwiJs.captureXhrRecorder.addTrigger('https://interface.music.163.com/weapi/song/enhance/player/url/v1?csrf_token', function (info) {
            //key,url,text
            try {
                top.window.kl.log("\n----------------------------------\ncaptureXhrRecorder audio_link_info\n", info.url,);
                let audio_info = JSON.parse(info.text);
                top.window.kl.kiwiJs.captureInfo.music163.audio_link = audio_info.data[0].url;
                top.window.kl.log('captureXhrRecorder audio_link_info', audio_info, audio_info.data[0].url);
                if (top.window.kl.kiwiJs.captureInfo.music163.lyric !== false && top.window.kl.kiwiJs.captureInfo.music163.audio_link !== false) {
                    top.window.kl.kiwiJs.captureXhrRecorder.stopScan();

                }
            } catch (e) {
                top.window.kl.kiwiJs.showMsg('音乐播放链接搞不出来了' + e.message);
                top.window.kl.log('captureXhrRecorder audio_link_info 音乐播放链接搞不出来了', info);

            }
        });


        /**
         * 网易云音乐的业务js 已经接受了 xhr record 清理工作，告诉默认的清理程序可以歇歇了
         */
        top.window.kl.kiwiJs.shutdowDefaultXhrStorageCleaner('music.164.com.js instead');


        /**
         * 一个简答的歌词 播放器
         * @param lyric_str
         * @return lyricPlayer
         */
        let lyricPlayer = function (lyric_str) {
            let self = new Emt('div', 'class="kl_kiwi_js_lyric_player"');
            self._config = {
                lastStrIndex: 0,
                timePos: [],
                timePMap: {},
                timeLastInfo: {arrayIndex: 0, sec: 0},
                height: 0,
                top: 0,
                pHeight: 0,
                init: false,
                scrollLock: true,
                durationSec: 0,
                lyricTypeIsStandard: false,
                diffHeight: 0,
                diffPHeight: 0,
            };
            self.config = self._config;

            self.setDuration = function (sec) {
                if (self.config.durationSec === 0) {
                    self.config.durationSec = sec;
                }
            };

            self.loadLyric = function (lyric_str) {
                self.innerHTML = '';
                self.config = self._config;
                self.style.marginTop = '0px';
                let tmp_strs = lyric_str.split("\n");
                let errors = [];

                let p1 = new Emt('p', '');
                let no_std_ps = [];
                let std_ps_cnt = 0;
                let error_std_ps = [];
                self.addNode(p1);
                tmp_strs.forEach(function (tmp_str) {
                    if (tmp_str.indexOf('[') === 0) {
                        let tmp_nums = tmp_str.substr(1, 5).split(':');
                        if (tmp_nums.length === 2) {
                            let num1 = parseInt(tmp_nums[0]);
                            let num2 = parseInt(tmp_nums[1]);
                            if (!(isNaN(num1) || isNaN(num2))) {
                                let sec = num1 * 60 + num2;
                                let p = new Emt('p', 'flag="' + self.config.timePos.length + '" sec="' + sec + '"', tmp_str);
                                self.config.timePos.push(sec);
                                self.config.timePMap['p' + sec] = p;
                                std_ps_cnt = std_ps_cnt + 1;
                                self.addNode(p);
                            } else {
                                //errors.push('开头isNaN:' + tmp_str);
                                let p = new Emt('p', '', tmp_str + '');
                                error_std_ps.push(p);
                                self.addNode(p);
                            }

                        } else {
                            let p = new Emt('p', '', tmp_str + '');
                            error_std_ps.push(p);
                            self.addNode(p);
                            //errors.push('开头多个[:]:' + tmp_str);
                        }
                    } else {
                        let p = new Emt('p', '', tmp_str + '');
                        no_std_ps.push(p);
                        self.addNode(p);
                        //errors.push('开头不能识别:' + tmp_str);
                    }
                });
                let unique = function (arr) {
                    return Array.from(new Set(arr)); // 利用Array.from将Set结构转换成数组 Set数据结构，它类似于数组，其成员的值都是唯一的
                };
                self.config.timePos = unique(self.config.timePos).sort(function (a, b) {
                    return a - b;
                });//push速度过快，导致顺序混乱，默认排序又不按数字来
                if (self.config.timePos.length > 9) {
                    self.config.timeLastInfo.sec = self.config.timePos[0];
                }
                let all_ps_total = std_ps_cnt + no_std_ps.length + error_std_ps.length;
                top.window.kl.log('xxxxx', std_ps_cnt, no_std_ps.length, error_std_ps.length);
                if ((std_ps_cnt / all_ps_total) > 0.7) {
                    self.config.lyricTypeIsStandard = true;//超过70%，就捏着鼻子认了
                }

                p1.textContent = errors.join('//');
                setTimeout(function () {
                    self.config.height = self.scrollHeight;
                    self.config.diffHeight = self.scrollHeight - self.offsetHeight;
                    self.config.diffPHeight = Math.round(self.config.diffHeight / (all_ps_total + 1));
                    //self.config.top = self.scroll.top;
                    self.config.pHeight = Math.round(self.config.height / (all_ps_total + 1));
                    top.window.kl.log('scroll2sec init', self.config, self.scrollHeight, self.offsetHeight, all_ps_total);
                    top.window.kl.log('scroll2sec height error', self.config.height);
                    self.config.init = true;
                    self.config.scrollLock = false;
                }, 300);
                return self;
            };

            self.scroll2sec = function (input_sec) {
                if (self.config.init === false) {
                    top.window.kl.log('scroll2sec self.config.init===false');
                    return false;
                }
                if (self.config.scrollLock === true) {
                    top.window.kl.log('scroll2sec self.config.scrollLock===true');
                    return false;
                }
                self.config.scrollLock = true;
                if (self.config.lyricTypeIsStandard === false) {
                    return self.scrollByRatio(input_sec);
                }

                let new_last_array_index = self.config.timeLastInfo.arrayIndex;
                let new_sec = 0;
                let tmp_start = 0;
                let tmp_end = self.config.timePos.length;
                if (self.config.timeLastInfo.sec < input_sec) {
                    tmp_start = self.config.timeLastInfo.arrayIndex;
                } else {
                    tmp_end = self.config.timeLastInfo.arrayIndex;
                }

                for (let tmp_i = tmp_start; tmp_i < tmp_end; tmp_i++) {
                    //console.log('scroll2sec ', 'i:', tmp_i, 'new_index:', new_last_array_index, 'time:', self.config.timePos[tmp_i], 'res:', sec < self.config.timePos[tmp_i]);
                    if (input_sec < self.config.timePos[tmp_i]) {
                        break;
                    }
                    new_last_array_index = tmp_i;
                    new_sec = self.config.timePos[tmp_i];
                }
                //  top.window.kl.log('scroll2sec ', 'input:', input_sec, 'start:', tmp_start, 'end:', tmp_end, 'cuurent:', self.config.timeLastInfo, 'new_index:', new_last_array_index,'new_sec:', new_sec);

                if (new_last_array_index !== self.config.timeLastInfo.arrayIndex) {
                    self.config.timePMap['p' + self.config.timePos[new_last_array_index]].classList.add('lyric_str_selected');
                    self.config.timePMap['p' + self.config.timeLastInfo.sec].classList.remove('lyric_str_selected');
                    top.window.kl.log('scroll2sec new posi', 'input:', input_sec, 'current :', self.config.timeLastInfo, 'to:', {
                        sec: new_sec,
                        arrayIndex: new_last_array_index
                    });
                    self.config.timeLastInfo.sec = new_sec;
                    self.config.timeLastInfo.arrayIndex = new_last_array_index;
                }
                if (self.config.timeLastInfo.arrayIndex > 6) {
                    //console.log('scroll2sec',self.config.timeLastInfo.arrayIndex - 6,self.config.pHeight,-(self.config.timeLastInfo.arrayIndex - 6) * self.config.pHeight);
                    self.style.marginTop = (-(self.config.timeLastInfo.arrayIndex - 6) * self.config.pHeight) + 'px';
                } else {
                    self.style.marginTop = '0px';
                }
                self.config.scrollLock = false;

            };
            /**
             * 没有时间点的歌词，直接按比例滚动，不管对错了
             * @param input_sec
             */
            self.scrollByRatio = function (input_sec) {
                self.config.scrollLock = true;
                if (self.config.durationSec > 0) {
                    self.style.marginTop = (-Math.round(input_sec * self.config.diffHeight / self.config.durationSec)) + 'px';
                } else {
                    self.style.marginTop = '0px';
                }
                self.config.scrollLock = false;
            };
            return self;
        };


        if (0) {
            //不生效，待琢磨   document.location.href === 'https://y.music.163.com/m/login?redirect_url=https%3A%2F%2Fy.music.163.com%2Fm%2Fplaylist%3Fid%3D40781021#/phone?type=mail'
            let log_btn = kl.xpathSearch('.//button[contains(text(),"登录")]')[0];
            let touch_event = document.createEvent('Events');
            touch_event.initEvent('touchend', true, true);
            log_btn.dispatchEvent(touch_event);
        }


        if (document.location.href.indexOf('https://y.music.163.com/m/playlist?id=') === 0) {
            let audio_ele = new Emt('audio', 'controls="controls" class="hide2"');//播放音乐的element
            let lyric_div = new Emt('div', 'style="height:75%;overflow:hidden;"');


            let songs_div = new Emt('div', ' class="kl_kiwi_list_box" ');//歌曲列表的root div
            let player_div = new Emt('div', 'class="kl_kiwi_play_box hide"');//歌词显示、audio element、播放控制 的 root div
            let songs_grid = (new HammerTvGrid()).setUIColsNumber(3).setUIRowsNumber(7);//歌曲列表的 网格
            let switch_grid = (new HammerTvGrid()).setUIColsNumber(3).setUIRowsNumber(1).setPros({className: 'hide'});//用于上一首 下一首 循环模式的  换曲网格
            let audio_grid = (new HammerTvGrid()).setUIColsNumber(1).setUIRowsNumber(1);//audio element 的网格 ，左右快进，下隐藏  player_div

            top.window.kl.kiwiJs.captureInfo.music163.player = audio_ele;


            top.window.kl.kiwiJs.root_div.addNodes([
                songs_div.addNode(
                    new Emt('div', ' class="kl_kiwi_list_box_title" ', '歌曲列表,一直按[↓]展开/收起'),
                    new Emt('div', ' class="kl_kiwi_list_box_list" ').addNode(songs_grid),
                )
            ]);
            top.window.kl.kiwiJs.root_div.addNodes([
                player_div.addNodes([
                    new Emt('iframe', 'display:block;float:left;width:100%;height:50%;', '', {
                        lastSrc: '',
                        sourceBtn: false
                    }).setIndexHandler(player_div, 'ifr'),
                    lyric_div,
                    new Emt('div', 'class="kl_kiwi_play_ctrl_box"').addNodes([
                        switch_grid,
                        audio_grid,
                    ])

                ])
            ]);

            lyric_div.addNodes([(new lyricPlayer()).setIndexHandler(lyric_div, 'player')]);

            audio_grid.setPros({className: 'kl_kiwi_audio_box'}).loadSourceArray([{
                text: '', tagName: 'button', type: 'fun', fun: function () {
                    //检测播放是否已暂停.audio.paused 在播放器播放时返回false.
                    if (audio_ele.paused) {
                        audio_ele.play();//audio.play();// 这个就是播放
                    } else {
                        audio_ele.pause();// 这个就是暂停
                    }
                }
            }]).loadGridData2UI();
            audio_grid.drawUIGrid().uiGrid[0][0].gridCellElement.addNodes([audio_ele,
                new Emt('p', '', '#').setIndexHandler(audio_grid, 'song_title')
            ]);


            // songs_grid.bindGrid('top', top.window.kl.kiwiJs.root_div.website_grid);
            songs_grid.setIndexHandler('songs_grid', top.window.kl.kiwiJs.root_div);
            switch_grid.setIndexHandler('switch_grid', top.window.kl.kiwiJs.root_div);

            top.window.kl.kiwiJs.root_div.website_grid.bindDirectionFunction('down', function () {
                top.window.kl.kiwiJs.root_div.website_grid.classList.add('hide');
                top.window.kl.kiwiJs.root_div.top_grid.classList.add('hide');
                songs_grid.focusUILastGridCell('website_grid');
            });//导航网格  向下超出时， 隐藏 自己，展示 歌曲列表 网格
            songs_grid.bindDirectionFunction('top', function () {
                top.window.kl.kiwiJs.root_div.website_grid.classList.remove('hide');
                top.window.kl.kiwiJs.root_div.top_grid.classList.remove('hide');
                top.window.kl.kiwiJs.root_div.website_grid.focusUILastGridCell('songs_grid');
            });//歌曲列表网格  向上超出时， 隐藏 自己，展示 导航网格

            songs_grid.bindDirectionFunction('down', function () {
                player_div.classList.remove('hide');
                switch_grid.focusUILastGridCell('songs_grid');
            });//歌曲列表网格  向下超出时， 隐藏 自己，展示 播放界面


           // switch_grid.bindGrid('down', audio_grid);

            switch_grid.bindDirectionFunction('down', function () {
                switch_grid.classList.add('hide');
                audio_grid.focusUILastGridCell('switch_grid');
            });//换曲控制网格  向下超出时， 隐藏 自己，焦点进入  audio element 网格

            audio_grid.bindDirectionFunction('top', function () {
                switch_grid.classList.remove('hide');
                switch_grid.focusUILastGridCell('audio_grid');
            });//audio element 网格  向上超出时， 隐藏 自己，展示并进入换曲控制网格

            audio_grid.bindDirectionFunction('left', function () {
                //快退
                audio_ele.currentTime = audio_ele.currentTime - 15;
            });
            audio_grid.bindDirectionFunction('right', function () {
                //快进
                audio_ele.currentTime = audio_ele.currentTime + 15;
            });

            audio_grid.bindDirectionFunction('down', function () {
                player_div.classList.add('hide');
                songs_grid.focusUILastGridCell('songs_grid');
            });//audio element 网格  向下超出时， 隐藏播放界面，展示歌曲列表网格


            switch_grid.loopType = 'list';

            switch_grid.preSong = function () {
                songs_grid.focusPreUIGridCell();
                top.window.kl.kiwiJs.golobInfo.lastUIGridCell.focusSelf();
                document.activeElement.play();
                // audio_ele.currentTime = audio_ele.currentTime - 15;
            };
            switch_grid.nextSong = function () {
                // audio_ele.currentTime = audio_ele.currentTime + 15;
                songs_grid.focusNextUIGridCell();
                top.window.kl.kiwiJs.golobInfo.lastUIGridCell.focusSelf();
                document.activeElement.play();
            };
            switch_grid.changeLoop = function (input_ele) {
                let tmp_map = {single: {next: 'list', text: '循环-列表'}, list: {next: 'single', text: '循环-单曲'}};
                let new_info = tmp_map[switch_grid.loopType];
                switch_grid.loopType = new_info.next;
                input_ele.textContent = '当前' + new_info.text;
                //alert('别试了，目前只能单曲循环');
            };
            switch_grid.hideArea = function () {
                songs_grid.focusUILastGridCell('switch_grid_hide');
                player_div.classList.add('hide');
            };


            window.setInterval(function () {
                if (audio_ele.paused) {
                    return false;
                }
                if (lyric_div.player) {
                    lyric_div.player.scroll2sec(Math.ceil(audio_ele.currentTime));
                }
            }, 1000);

            let listen_songinfo = false;
            switch_grid.loadSongInfo = function (audio_link, lyric) {
                player_div.ifr.lastSrc = player_div.ifr.src;
                player_div.ifr.src = 'about:blank';
                top.window.kl.log('switch_grid.loadSongInfo', player_div.ifr.src, "\nudio_link:\n", audio_link, "\nlyric:\n", lyric);
                listen_songinfo = false;
                top.window.kl.kiwiJs.stopRecordXhr();
                audio_ele.src = audio_link;
                //lyric_div.textContent = lyric;
                if (lyric_div.player) {
                    lyric_div.player.remove();
                }
                lyric_div.addNodes([(new lyricPlayer()).setIndexHandler(lyric_div, 'player')]);

                lyric_div.player.loadLyric(lyric);
                //https://blog.csdn.net/qq_36267404/article/details/99445194
                audio_grid.song_title.textContent = player_div.ifr.sourceBtn.init_data.text;


                audio_ele.addEventListener("canplay", function () {   //当浏览器能够开始播放指定的音频/视频时，发生 canplay 事件。
                    //Math.ceil(audio_ele.duration)
                    top.window.kl.log('canplay', audio_ele.duration);
                    lyric_div.player.setDuration(Math.ceil(audio_ele.duration));
                    audio_ele.play();
                });
                audio_ele.addEventListener("ended", function () {   //首歌曲播放完之后。
                    if (switch_grid.loopType === 'single') {
                        audio_ele.currentTime = 0;
                        audio_ele.play();
                    } else if (switch_grid.loopType === 'list') {
                        switch_grid.nextSong();
                    } else {
                        top.window.kl.log('暂不支持 switch_grid.loopType:', switch_grid.loopType);

                    }
                });
                // kl.xpathSearch('.//div[contains(@class," m-song-clickarea m-song-clickarea2")]', ifr.contentDocument)[0].click()
            };


            //captureSongInfoInIframe 逻辑


            window.setInterval(function () {
                if (listen_songinfo) {
                    if (top.window.kl.kiwiJs.captureInfo.music163.lyric !== false && top.window.kl.kiwiJs.captureInfo.music163.audio_link !== false) {
                        // kl.xpathSearch('.//div[contains(@class," m-song-clickarea m-song-clickarea2")]',tmp_fir.contentDocument)[0].click()
                        top.window.kl.log('iframe_loaded audio_link lyric');//见鬼，频繁触发，解除不掉
                        switch_grid.loadSongInfo(top.window.kl.kiwiJs.captureInfo.music163.audio_link, top.window.kl.kiwiJs.captureInfo.music163.lyric);
                    }
                }

            }, 100);
            player_div.ifr.addEventListener('load', function () {
                if (this.src === 'about:blank') {
                    top.window.kl.log('player_div.ifr.iframe.loaded nothing', player_div.ifr.src);
                    player_div.ifr.classList.add('hide');
                } else {
                    listen_songinfo = true;
                    top.window.kl.log('player_div.ifr.iframe.loaded listen_songinfo', player_div.ifr.src);
                }
            });

            switch_grid.captureSongInfoInIframe = function (tmp_link, source_btn) {
                if (player_div.ifr.lastSrc === tmp_link) {
                    player_div.classList.remove('hide');
                    audio_grid.focusUILastGridCell('songs_grid');
                    return false;
                }
                player_div.classList.remove('hide');
                audio_grid.focusUIGridCell(0, 0, 'init');

                top.window.kl.kiwiJs.captureInfo.music163.lyric = false;
                top.window.kl.kiwiJs.captureInfo.music163.audio_link = false;
                top.window.kl.kiwiJs.keepRecordXhr();
                top.window.kl.kiwiJs.captureXhrRecorder.keepScan();
                top.window.kl.kiwiJs.captureXhrRecorder.cleanXhrRecord(false);//全都清理掉，不管了

                top.window.kl.log('清理 captureSongInfoInIframe lyric & audio_link clean');
                setTimeout(function () {
                    player_div.ifr.classList.remove('hide');
                    player_div.ifr.src = tmp_link;
                    player_div.ifr.sourceBtn = source_btn;
                }, 300);//因为 keepRecordXhr 在设置后，不能立马生效，所以延迟下，后期考虑把循环事件设置小一些

            };

            let player_btns = [
                {text: '上一首', handleKey: 'home', type: 'fun', fun: switch_grid.preSong},
                {text: '下一首', handleKey: 'pre', type: 'fun', fun: switch_grid.nextSong},
                {
                    text: '循环-列表', handleKey: 'pre', type: 'fun', fun: function (ele) {
                        switch_grid.changeLoop(ele);
                    }
                },
            ];
            switch_grid.loadSourceArray(player_btns).loadGridData2UI();
            top.window.kl.kiwiJs.showMsg('正在加载歌曲');


            top.window.kl.kiwiJs.keepRecordXhr();
            top.window.kl.kiwiJs.captureXhrRecorder.keepScan();
            top.window.kl.kiwiJs.captureXhrRecorder.cleanXhrRecord(false);//全都清理掉，不管了

            setTimeout(function () {
                player_div.ifr.src = document.location.href;
            }, 300);//因为 keepRecordXhr 在设置后，不能立马生效，所以延迟下，后期考虑把循环事件设置小一些


            top.window.load_song_si = window.setInterval(function () {
                top.window.kl.log('top.window.kl.kiwiJs.captureInfo.music163.playlist.isCompleted', top.window.kl.kiwiJs.captureInfo.music163.playlist.isCompleted);
                if (top.window.kl.kiwiJs.captureInfo.music163.playlist.isCompleted === false) {
                    return false;
                }
                kl.xpathSearch('.//div[@class="pylst_list"]')[0].remove();
                window.clearInterval(window.load_song_si);
                let song_btns = [];
                top.window.kl.kiwiJs.captureInfo.music163.playlist.songs.forEach(function (info, info_index) {
                    if (1 || info_index < 100) {
                        song_btns.push({
                            text: info_index + '/' + info.text + "\n" + info.desc, link: info.link, type: 'fun', fun: function (emt) {
                                top.window.kl.log('xxxxxxxxxx', emt.init_data);
                                switch_grid.captureSongInfoInIframe(info.link, emt);
                            }
                        });
                    }
                });
                songs_grid.loadSourceArray(song_btns).loadGridData2UI();
                top.window.kl.kiwiJs.showMsg('歌曲加载完毕.共' + song_btns.length + '首,向下进入歌曲列表', 1);
                top.window.kl.log('songs_grid.dataGrid', songs_grid.dataGrid);
                // songs_table.selectCell(0, 0, 'init');
                top.window.kl.kiwiJs.root_div.website_grid.focusUILastGridCell('next_grid');

            }, 1000);


            window.songs_css_si = window.setInterval(function () {
                if (songs_div.offsetHeight > 0) {
                    window.clearInterval(window.songs_css_si);
                    let avg_w = Math.floor(songs_div.offsetWidth / songs_grid.config.ui.cols);
                    let avg_h = Math.floor(songs_div.offsetHeight / (songs_grid.config.ui.rows + 0.5));
                    top.document.body.append(new Emt('style', 'id="kl_kiwi_menu_style_website"', '' +
                        '.kl_kiwi_list_box_list>table tr td{ width:' + avg_w + 'px;height:' + avg_h + 'px; }' +
                        '.kl_kiwi_list_box_list>table tr td>button{display:block; width:90%;height:90%;    text-align: left;font-size:14px; }' +
                        '.kl_kiwi_play_box {position: fixed; left: 0; top: 0;width: 100%;height: 100%; background: #CCC;z-index:999999}' +
                        '.kl_kiwi_js_lyric_player{height:100%;}' +
                        '.lyric_str_selected{font-weight:900;}' +
                        '.hide2:focus:{border:1em solid #F00;}' +
                        '.a:focus:{display:block;float:left;border:1em solid #F00;}' +
                        '.kl_kiwi_audio_box,.kl_kiwi_audio_box>*,.kl_kiwi_audio_box tr,.kl_kiwi_audio_box td,.kl_kiwi_audio_box button{display:block;float:left;width:100%;}' +
                        '.kl_kiwi_audio_box audio{width:100%;height:0.6em;}' +
                        '.kl_kiwi_play_ctrl_box{position: fixed;width:100%;bottom:0px;}' +

                        ''));
                }
            }, 100);

            window.addEventListener('1beforeunload', (event) => {
                // Cancel the event as stated by the standard.
                event.preventDefault();
                // Chrome requires returnValue to be set.
                event.returnValue = 'xxxx ';
            });


        }
    }


)();

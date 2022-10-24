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

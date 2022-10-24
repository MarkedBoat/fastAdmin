/**
 * 通过路径获取 变量的某个属性 值
 * @param object
 * @param keysPath
 * @returns {*|undefined}
 */
KL.prototype.getObjValByPath = function (object, keysPath) {
    let keys = keysPath.split('.');
    let last = keys.splice(-1);
    return [object].concat(keys).reduce(function (a, b) {
        if (typeof a[b] === 'undefined') a[b] = {};
        return a[b]
    })[last] || undefined;
};
/**
 * 通过 路径  设置 变量的某个属性 值
 * @param object
 * @param keysPath
 * @param value
 * @param isArrayElement
 * @returns {boolean}
 */
KL.prototype.setValByPath = function (object, keysPath, value, isArrayElement = false) {
    let keys = keysPath.split('.');
    let last = keys.splice(-1);
    if (isArrayElement) {
        [object].concat(keys).reduce(function (a, b) {
            if (typeof a[b] === 'undefined') a[b] = {};
            return a[b]
        })[last].push(value);
    } else {
        [object].concat(keys).reduce(function (a, b) {
            if (typeof a[b] === 'undefined') a[b] = {};
            return a[b]
        })[last] = value;
    }

    return true;
};


KL.prototype.packageDataInBatch = function (opt_input) {
    let self = {
        isOK: false,
        input: opt_input || {
            srcRows: [
                {
                    field1: 111,
                    field2: 222,
                    __on_match: function (srcRowData, resRowData) {
                        //
                    },
                    __on_empty: function (srcRowData) {
                    },
                },
            ],
            srcKey: 'field2',
            resKey: 'res_key',//queryDataFun返回结果中，如果  res_key的值 ===  field2的值，即视为 matched，触发 on_match，如果没有匹配的触发 on_empty
            queryResFun: function (src_vals, callback_package_fun) {
                if (true) {
                    callback_package_fun({status: true, res: [{}, {}, {}, {}]});//正常返回的结果
                } else if (true) {
                    //自行处理
                    callback_package_fun({status: false, res: 'retry'});
                } else {
                    //自行抛错
                    callback_package_fun({status: false, res: 'error'});
                }
            },
            onResMatch: function (srcRowData, resRowData) {
                //同上面  __on_match ,__on_match  优先，可定制,onResMatch 是共同处理
            },
            onResEmpty: function (srcRowData) {
                //同 resMatch  __on_empty
            },
            onChunkChange: function (info) {

            },
            onSuccess: function (info) {
                //需要重写，处理成功后  会调用这个方法，
            },
            onError: function (errorMsg) {
                alert('出错了');//定制报错的信息
            },
        }
    };
    self.chunks = [];

    self.tryAppend = function () {
        let tmp_size = 100;
        let tmp_cnt = Math.ceil(self.input.srcRows.length / tmp_size);
        for (let tmp_i = 0; tmp_i < tmp_cnt; tmp_i++) {
            let tmp_rows = self.input.srcRows.slice(tmp_i * tmp_size, (tmp_i + 1) * tmp_size);
            self.chunks.push({index: self.chunks.length, res: false});
            self.queryChunk(self.chunks.length - 1, tmp_rows, 0);
            //let tmp_res=
        }
        //let tmp_chunks=
    };
    self.onChunkOk = function (chunk_index) {
        self.chunks[chunk_index].isOK = true;
        let tmp_ok_cnt = 0;
        let tmp_wait_cnt = 0;
        self.chunks.forEach(function (chunk) {
            if (chunk.isOK) {
                tmp_ok_cnt += 1;
            } else {
                tmp_wait_cnt += 1;
            }
        });
        console.log('chunk process : all ', self.chunks.length, ' ok:', tmp_ok_cnt, ' wait:', tmp_wait_cnt);
        if (typeof self.input.onChunkChange === 'function') {
            self.input.onChunkChange({all: self.chunks.length, ok: tmp_ok_cnt, wait: tmp_wait_cnt});
        }
        if (tmp_ok_cnt === self.chunks.length) {
            self.input.onSuccess({all: self.chunks.length, ok: tmp_ok_cnt, wait: tmp_wait_cnt});
        }
    };

    self.queryChunk = function (chunk_index, src_rows, try_times) {
        console.log(src_rows, try_times);
        let tmp_src_vals = [];
        let tmp_src_map = {};
        src_rows.forEach(function (src_row, tmp_row_index) {
            tmp_src_vals.push(src_row[self.input.srcKey]);
            if (typeof tmp_src_map[src_row[self.input.srcKey]]) {
                tmp_src_map['src_' + src_row[self.input.srcKey]] = [];
            }
            tmp_src_map['src_' + src_row[self.input.srcKey]].push(src_row);
        });
        let src_keys = [];
        self.input.queryResFun(tmp_src_vals, function (tmp_res) {
            if (tmp_res.status === true) {
                let tmp_res_map = {};
                tmp_res.res.forEach(function (tmp_res_row) {
                    let src_key = 'src_' + tmp_res_row[self.input.resKey];
                    src_keys.push(src_key);
                    if (tmp_src_map[src_key] !== undefined) {
                        tmp_src_map[src_key].forEach(function (tmp_src_row) {
                            if (typeof tmp_src_row.__on_match === 'function') {
                                tmp_src_row.__on_match(tmp_src_row, tmp_res_row);
                            } else if (typeof self.input.onResMatch === 'function') {
                                self.input.onResMatch(tmp_src_row, tmp_res_row);
                            } else {

                            }
                        });
                    }
                    tmp_res_map['tmp_' + tmp_res_row[self.input.resKey]] = tmp_res_row;
                });
                src_rows.forEach(function (tmp_src_row) {
                    let src_key = 'src_' + tmp_src_row[self.input.srcKey];
                    if (src_keys.indexOf(src_key) === -1) {
                        if (typeof tmp_src_row.__on_empty === 'function') {
                            tmp_src_row.__on_empty(tmp_src_row);
                        } else if (typeof self.input.onResEmpty === 'function') {
                            self.input.onResEmpty(tmp_src_row);
                        } else {

                        }
                    }
                });
                self.onChunkOk(chunk_index);

            } else if (tmp_res.res && tmp_res.res === 'retry') {
                self.queryChunk(chunk_index, src_rows, try_times + 1);
            } else {
                return false;
            }
        });

    };
    self.tryAppend();

    return self;
};
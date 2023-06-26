(() => {
    /**
     *
     * @param beCheckedData
     * @param checkConfig
     * @param parentFlag
     * @returns {boolean|[]}  true无错误，[错误消息1 ，错误消息2 …… ]
     */
    let basicCheck = (beCheckedData, checkConfig, parentFlag) => {
        // console.log([beCheckedData, checkConfig]);
        let errors = [];
        checkConfig.detail = checkConfig.detail || '';
        if (checkConfig.attr === 'root') {
            checkConfig.check.objectAttrs.forEach((subCheckConfig) => {
                let sub_res = basicCheck(beCheckedData, subCheckConfig, 'root');
                if (sub_res !== true) {
                    errors = errors.concat(sub_res);
                }
            });

            if (errors.length === 0) {
                return true
            } else {
                return errors;
            }
        } else {
            let dataAttrType = typeof beCheckedData[checkConfig.attr];
            if (dataAttrType === 'object' && typeof beCheckedData[checkConfig.attr].forEach === 'function') {
                dataAttrType = 'array';
            }
            //console.log(checkConfig.attr, dataAttrType,checkConfig.check.type);
            if (checkConfig.check.must === true && dataAttrType === 'undefined') {
                errors.push(`${parentFlag}.${checkConfig.attr} [${checkConfig.detail}] 未设置`);
                return errors;
            }

            if (checkConfig.check.type.indexOf(dataAttrType) === -1) {
                errors.push(`数据 ${parentFlag}.${checkConfig.attr} [${checkConfig.detail}] 是 ${dataAttrType} 不在预期内`);
                //  console.log(errors);
                return errors;
            }
            if (dataAttrType === 'number') {
                if (checkConfig.check.min !== undefined && beCheckedData[checkConfig.attr] < checkConfig.check.min) {
                    errors.push(`数据 ${parentFlag}.${checkConfig.attr} [${checkConfig.detail}] 比最小值 [${checkConfig.check.min}] 小`);
                }
                if (checkConfig.check.max !== undefined && beCheckedData[checkConfig.attr] > checkConfig.check.max) {
                    errors.push(`数据 ${parentFlag}.${checkConfig.attr} [${checkConfig.detail}] 比最大值 [${checkConfig.check.max}] 大`);
                }
                if (errors.length === 0) {
                    return true
                } else {
                    return errors;
                }
            }

            if (dataAttrType === 'array') {

                beCheckedData[checkConfig.attr].forEach((attrArrayElement, attrArrayElementIndex) => {
                    let tmpDataType = typeof attrArrayElement;
                    if (tmpDataType === 'object' && typeof attrArrayElement.forEach === 'function') {
                        tmpDataType = 'array';
                    }
                    if (checkConfig.check.arrayElementConfig.check.type.indexOf(tmpDataType) === -1) {
                        errors.push(`数组 ${parentFlag}.${checkConfig.attr}[${attrArrayElementIndex}]  [${checkConfig.detail}] 是 ${tmpDataType} 不在预期内`);
                    }
                    if (tmpDataType === 'object') {
                        // console.log(checkConfig.check);
                        checkConfig.check.arrayElementConfig.check.objectAttrs.forEach((attrArrayElementConfig) => {
                            let sub_res = basicCheck(attrArrayElement, attrArrayElementConfig, `${parentFlag}.${checkConfig.attr}[${attrArrayElementIndex}]`);
                            if (sub_res !== true) {
                                errors = errors.concat(sub_res);
                            }
                        });
                    }
                });
                if (errors.length === 0) {
                    return true
                } else {
                    return errors;
                }
            }

            if (dataAttrType === 'object') {
                checkConfig.check.objectAttrs.forEach((subCheckConfig) => {
                    let sub_res = basicCheck(beCheckedData[checkConfig.attr], subCheckConfig, `${parentFlag}.${checkConfig.attr}`);
                    if (sub_res !== true) {
                        errors = errors.concat(sub_res);
                    }
                });
                if (errors.length === 0) {
                    return true
                } else {
                    return errors;
                }
            } else {
                return true;
            }
        }
    };

    /**
     *
     * @type {basicCheck}
     */
    kl.dataBasicCheck = basicCheck;

    if (0) {
        //eg
        let egData = {
            handleKey: 'id',
            attrKey: false,
            headerText: false,
            sortable: true,
            filter: {
                inputs: [
                    true,
                ],
                config: {
                    valueItems: [{text: 'text', val: 'val'}],
                }
            },
            info: [],
            fun: 1,
        };
        let egCheckConfig = {
            attr: 'root',
            check: {
                must: true,
                type: ['object'],
                objectAttrs: [
                    {attr: 'handleKey', check: {must: true, type: ['string']}},//句柄
                    {attr: 'attrKey', check: {must: true, type: ['string', 'boolean']}},//数据下标
                    {attr: 'headerText', check: {must: true, type: ['string']}},
                    {attr: 'sortable', check: {must: true, type: ['boolean']}},
                    {
                        attr: 'filter',
                        check: {
                            must: false,
                            type: ['boolean', 'object'],
                            objectAttrs: [
                                {
                                    attr: 'inputs',
                                    check: {
                                        must: true,
                                        type: ['array'],
                                        arrayElementConfig: {
                                            check: {
                                                type: ['boolean', 'object'],
                                                objectAttrs: [
                                                    // {attr: 'tagName', check: {must: true, type: ['string']}},
                                                ]
                                            }
                                        },
                                    }
                                },
                                {
                                    attr: 'config',
                                    check: {
                                        must: true,
                                        type: ['object'],
                                        objectAttrs: [
                                            {
                                                attr: 'valueItems',
                                                check: {
                                                    must: true,
                                                    type: ['array'],
                                                    arrayElementConfig: {
                                                        check: {
                                                            type: ['object'],
                                                            objectAttrs: [
                                                                {attr: 'val', check: {must: true, type: ['string', 'number']}},
                                                                {attr: 'text', check: {must: true, type: ['string', 'number']}}
                                                            ]
                                                        }
                                                    }
                                                },
                                            }
                                        ]
                                    }
                                },
                            ]
                        }
                    },
                    {attr: 'info', check: {must: false, type: ['object'], objectAttrs: []}},
                    {attr: 'fun', check: {must: true, type: ['boolean', 'function']}},
                ]
            }
        };

        let check_res = basicCheck(egData, egCheckConfig, 'root');
        console.log(check_res);

    }

})();

<?php

namespace models\common\sys;

interface IDispatcher
{
    public function outLastErrorAndExit();

    /**
     * 创建一个 中断信息 ，可用与当作错误返回
     * @param string $detail_code 描述code
     * @param string $outer_msg 对外展示 消息提示
     * @param mixed $outer_data 对外展示的 数据
     * @param array $debug_data 调试信息，只在debug的时候限时
     * @return array
     */
    public function createInterruption($detail_code, $outer_msg, $outer_data, $debug_data = []);
}


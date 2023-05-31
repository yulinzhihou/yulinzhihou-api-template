<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'key:generate'      =>  'app\command\Key',
        'yc:create'         =>  'app\command\YC',
        'ym:create'         =>  'app\command\YM',
        'yv:create'         =>  'app\command\YV',
    ],
];

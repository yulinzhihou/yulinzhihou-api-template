<?php

use think\facade\Route;

return [
    Route::miss(function(){
        return json([
            'code'      => 1,
            "status"    =>  59994,
            'message'   =>  '[Index]路由地址未定义,不支持直接请求，请使用正确的接口地址和参数，请联系后端小哥哥，QQ:841088704',
            'data'      => [
                'method'    =>  request()->method(),
                'route'     =>  request()->url(),
                'params'    =>  request()->param(),
            ],
            'time'      =>  time(),
            'type'      =>  'ERROR',
            'date'      =>  date("Y-m-d H:i:s",time())
        ]);
    })
];
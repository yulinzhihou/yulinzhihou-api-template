<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

return [
    Route::miss(function(){
        return json([
            "status"    =>  999,
            'message'   =>  '路由地址未定义,不支持直接请求，请使用正确的接口地址和参数，请联系后端小哥哥，QQ:841088704',
            'method'    =>  request()->method(),
            'route'     =>  request()->url(),
            'params'    =>  request()->param(),
            'create_time'   =>  time(),
            'date_time'     =>  date("Y-m-d H:i:s",time())
        ]);
    })
];
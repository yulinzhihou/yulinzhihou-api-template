<?php

use app\admin\middleware\checkSign;
use think\facade\Route;

return [
    Route::group('v1', function () {
        /*后台登录接口*/
        Route::group('login', function () {
            Route::get('index', 'Login@index');
            Route::get('captcha', 'Login@captcha');
            Route::post('login', 'Login@login');
            Route::any('logout', 'Login@logout');
        });

        /*后台管理员模块路由*/
        Route::group('admin', function () {
            Route::get('get_user_auth','Admin@getUserAuth');
            Route::post('upload','Admin@upload');
            Route::any('get_icons','Admin@getIcons');
        });
        Route::resource('admin','Admin')->expect(['create','edit']);

        /*菜单组*/
        Route::group('menu', function () {
            Route::post('sortable','Menu@sortable');
        });
        Route::resource('menu','Menu')->expect(['create','edit']);

        /*角色组*/
        Route::resource('role','Role')->expect(['create','edit']);

        /*管理员日志即接口日志*/
        Route::resource('api_log','ApiLog')->only(['index','info']);

        /*异常日志*/
        Route::resource('exception_log','ExceptionLog')->only(['index','info']);

        /*附件上传模块*/
        Route::resource('attachment','Attachment')->expect(['create','edit']);

        /*系统配置模块*/
        Route::resource('system_config','SystemConfig')->expect(['create','edit']);

    })->prefix('app\admin\controller\v1\\')->middleware([
        checkSign::class
    ]),

    Route::miss(function(){
        return json([
            'code'      => 1,
            "status"    =>  594,
            'message'   =>  '路由地址未定义,不支持直接请求，请使用正确的接口地址和参数，请联系后端小哥哥，QQ:841088704',
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
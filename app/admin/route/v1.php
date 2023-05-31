<?php

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

        /*后台控制台首页*/
        Route::resource('dashboard', 'Index')->only(['index']);

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

        /*游戏版本管理模块*/
        Route::resource('version','VersionBase')->expect(['create','edit']);

        /*游戏文件管理模块*/
        Route::group('version_files_config', function () {
            Route::get('file_type','VersionFilesConfig@fileTypeList');
            Route::get('async_file','VersionFilesConfig@asyncFile');
            Route::get('auto_file_upload','VersionFilesConfig@autoUploadFiles');
        });
        Route::resource('version_files_config','VersionFilesConfig')->expect(['create','edit']);

        /*游戏物品文件*/
        Route::resource('common_item','CommonItem')->only(['index','info']);

        /*游戏装备文件*/
        Route::resource('equip_base','EquipBase')->only(['index','info']);

        /*游戏宝石文件*/
        Route::resource('gem_info','GemInfo')->only(['index','info']);

        /*游戏宝石文件*/
        Route::resource('pet_attr','PetAttr')->only(['index','info']);

        /*游戏怪物NPC文件*/
        Route::resource('monster','Monster')->only(['index','info']);

        /*开服配置*/
        Route::group('server',function () {
            Route::post('test_db','Server@testDBLinkStatus');
        });
        Route::resource('server','Server')->expect(['create','edit']);

        /*离线GM配置*/
        Route::group('gm_config',function () {
            Route::post('build_config','GmConfig@buildConfig');
        });
        Route::resource('gm_config','GmConfig')->expect(['create','edit']);

        /*爆率文本生成 */
        Route::resource('drop_manual','DropManual');

        /*爆率文本生成 */
        Route::resource('article','Article');

        /*离线GM工具-账号配置*/
        Route::resource('account','Account');

        /*离线GM工具-角色配置*/
        Route::group('char', function () {
            Route::get('select_list', 'Char@selectList');
        });
        Route::resource('char','Char');

        /*在线GM通讯加密配置*/
        Route::group('api_config',function () {
            Route::post('build_config','ApiConfig@buildConfig');
        });
        Route::resource('api_config','ApiConfig');

        /*在线GM发货*/
        Route::group('gm_online', function () {
            Route::get('type','GmOnline@typeList');
            Route::get('select_list','GmOnline@selectList');
        });
        Route::resource('gm_online','GmOnline');

    })->prefix('app\admin\controller\v1\\')->middleware([
        \app\admin\middleware\checkSign::class
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
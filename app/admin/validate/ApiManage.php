<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class ApiManage extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|'	=>	['number','integer'],
		'app_id|应用APPID'	=>	['length:0,59'],
		'app_secret|应用APP_SECRET'	=>	['length:0,254'],
		'public_key|应用公钥'	=>	['length:0,65534'],
		'private_key|应用私钥'	=>	['length:0,65534'],
		'username|用户名'	=>	['length:0,59'],
		'phone|手机'	=>	['length:0,19'],
		'email|邮箱'	=>	['length:0,127'],
		'nickname|真实姓名'	=>	['length:0,127'],
		'password|密码'	=>	['length:0,127'],
		'salt|密码盐'	=>	['length:0,31'],
		'avatar|头像'	=>	['length:0,254'],
		'desc|描述'	=>	['length:0,254'],
		'status|状态：1=正常，0=禁用'	=>	['egt:0','in:0,1','number','integer'],
		'login_ip|最近登录IP'	=>	['length:0,127'],
		'login_failure|登录失败次数'	=>	['egt:0','number','integer'],
		'extension|扩展信息'	=>	['length:0,-1'],
		'login_time|最后一次登录时间'	=>	['egt:0','number','integer'],

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];


    /**
     * 验证场景
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $scene = [
        'index'	=>	[],
		'save'	=>	[
            'app_id','app_secret','public_key','private_key','username','phone','email','nickname',
            'password','salt','avatar','desc','status','login_ip','login_failure','extension',
            'login_time','create_time','update_time'
        ],
		'update'	=>	[
            'id','app_id','app_secret','public_key','private_key','username','phone','email','nickname',
            'password','salt','avatar','desc','status','login_ip','login_failure','extension',
            'login_time','create_time','update_time'
        ],
		'read'	=>	['id'],
		'delete'	=>	['id'],
		'changeStatus'	=>	['id'],
		'sortable'	=>	['id'],

    ];
}

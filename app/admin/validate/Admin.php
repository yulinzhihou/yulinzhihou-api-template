<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|ID'          =>  ['require','gt:0'],
        'username|用户名' => ['require', 'length:2,60','unique:admin,username'],
        'password|密码'   => ['length:0,128'],
        'desc|描述'       => ['length:0,255'],

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

    protected $scene = [
        'read'  =>  ['id'],
        'save'  =>  ['username','password','desc','avatar','phone','email','status'],
        'delete'   =>  ['id']
    ];
}

<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class AdminRole extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|ID'	            =>	 ['require'],
        'admin_id|管理员ID'	=>	 ['require','unique:admin_role,admin_id'],
        'role_id|角色ID'	    =>	 ['require','unique:admin_role,role_id'],
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
        'index' => [],
        'save'  => ['admin_id','role_id'],
        'read'  => ['id'],
        'delete'=> ['id']
    ];
}

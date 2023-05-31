<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|ID'	        =>	['require','gt:0'],
        'pid|父级ID'	    =>	['require','gt:0'],
        'name|角色名'	=>	['require','length:0,128'],
        'value|角色标识'	=>	['length:0,128'],
        'menu|菜单ID'	=>	['require','length:0,65535'],
        'remark|备注'	=>	['length:1,255'],
        'sort|排序'	    =>	['between:0,999'],
        'status|状态'	=>	['in:0,1']
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];


    protected $scene = [
        'index' =>  [],
        'save'  =>  ['pid','name','value','menu','remark','sort','status'],
        'read'  =>  ['id'],
        'update'=>  ['id'],
        'delete'=>  ['id'],
    ];
}

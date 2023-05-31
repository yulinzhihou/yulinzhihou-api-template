<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        "component|组件地址"	    =>	["length:0,128"],
        "extend|拓展"	        =>	["length:0,65530"],
        "icon|图标"	            =>	["length:0,255"],
        "id|ID"	                =>	['require',"gt:0"],
        "keep_alive|是否需要缓存该路由：1是，0否"	=>	["egt:0",'in:0,1'],
        "menu_type|菜单类型"	    =>	["length:0,128"],
        "name|路由名称"	        =>	["length:0,128"],
        "path|路由地址"	        =>	["length:0,128"],
        "pid|父ID"	            =>	["egt:0"],
        "remark|备注标记"	    =>	["length:0,255"],
        "status|状态：为1正常，为0禁用"	=>	["egt:0",'in:0,1'],
        "title|页面title"	    =>	["length:0,128"],
        "type|类型"	            =>	["length:0,128"],
        "url|重定向地址"	        =>	["length:0,255"],
        "weigh|菜单排序,只对第一级有效：0-65535"    =>	["egt:0"],
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

    protected $scene = [
        'index' => [],
        'read' => ['id'],
        'save' => ["pid","name","path","component","title","icon","url","remark","type","menu_type","extend","keep_alive","weigh","status"],
        'update' => ['id',"pid","name","path","component","title","icon","url","remark","type","menu_type","extend","keep_alive","weigh","status"],
        'delete' => ['id'],
    ];
}

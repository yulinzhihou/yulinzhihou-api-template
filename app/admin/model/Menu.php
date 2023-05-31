<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;
use think\helper\Str;

/**
 * 菜单模型
 */
class Menu extends Base
{
    protected $schema = [
        'id'	                =>	'int',
        'pid'	                =>	'int',
        'name'	                =>	'string',
        'path'	                =>	'string',
        'component'	            =>	'string',
        'title'	                =>	'string',
        'icon'	                =>	'string',
        'url'	                =>	'string',
        'keep_alive'	        =>	'int',
        'type'	                =>	'int',
        'menu_type'	            =>	'string',
        'extend'	            =>	'string',
        'weigh'	                =>	'int',
        'status'	            =>	'int',
        'create_time'	        =>	'int',
        'update_time'	        =>	'int'
    ];

    /**
     * 通过角色ID获取菜单权限
     * @param $roleId
     * @return array
     */
    public function routersByTree($roleId):array
    {
        try {
            $role = Role::find($roleId);
            if ($role) {
                $where[] = $role['menu'] == '*' ? true : ['id', 'in', explode(',', $role['menu'])];
            } else {
                $where[] = false;
            }
            $order = ['weigh' => 'desc'];  // 按排序序号由大到小排序（0-99）
            $whereStatus = $roleId == 1 ? [] : ['status'=>1];
            $result = $this->withoutField('create_time')->where($whereStatus)->where($where)->order($order)->select()->toArray();
            return !empty($result) ? tree($result) : [];
        } catch (\Exception $e) {
            Log::sql($e->getMessage());
            return [];
        }
    }


}

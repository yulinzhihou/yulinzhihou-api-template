<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;

/**
 * 角色模型
 */
class Role extends Base
{
    protected $schema = [
        'id'	        =>	'int',
        'pid'	        =>	'int',
        'name'	        =>	'string',
        'value'	        =>	'string',
        'menu'	        =>	'string',
        'remark'	    =>	'string',
        'sort'	        =>	'int',
        'status'	    =>	'int',
        'create_time'	=>	'int',
        'update_time'	=>	'int'
    ];

    /**
     * 获取详情
     * @param int $id 主键
     * @param array $condition 查询条件,默认查全部 ，如状态['status' => 1]
     * @param array $field  字段筛选
     * @return array
     */
    public function getInfo(int $id,array $condition = [],array $field = []) : array
    {
        try {
            $result = $this->field($field)->where($condition)->find($id);
            if ($result) {
                $result = $result->toArray();
                $result['menu'] = explode(',',$result['menu']);
                return $result;
            }
            return [];
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }
    }


    /**
     * 查询角色是否被其他地方使用
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUsedDataById(int $id) :bool
    {
        $hasChild = $this->where('pid',$id)->limit(1)->select()->toArray();
        if (!empty($hasChild)) {
            // 表示有下级
            return true;
        }
        // 查有没有被使用
        $hasUsedByAdmin = Admin::where('role_id',$id)->limit(1)->select()->toArray();
        if (!empty($hasUsedByAdmin)) {
            // 表示被管理员使用
            return true;
        }
        // 表示没有下级分类，也没有被管理员使用
        return false;
    }


    /**
     * 通过角色id 获取菜单
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuByRoleId($id):array
    {
        if (isset($id) && $id > 0) {
            $data = [];
            if ($id == 1) {
                $data = Menu::select()->column('permission');
            } else {
                $temp = $this->where('id',$id)->select()->toArray();
                if (!empty($temp[0]) && $temp[0]['menu'] != '') {
                    $data = Menu::where(['id', 'in', explode(',', $temp[0]['menu'])])->select()->toArray();
                }
            }
            return $data;
        } else {
            return [];
        }
    }
}

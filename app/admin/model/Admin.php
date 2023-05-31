<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;

/**
 * 后台管理员模型
 */
class Admin extends Base
{

    protected $schema = [
        "id"	    =>	"int",
        "username"	=>	"string",
        "password"	=>	"string",
        "salt"	    =>	"string",
        "nickname"	=>	"string",
        "role_id"	=>	"int",
        "login_failure"	=>	"int",
        "login_time"	=>	"int",
        "avatar"	=>	"string",
        "desc"	    =>	"string",
        "phone"	    =>	"string",
        "email"	    =>	"string",
        "extension"	=>	"string",
        "sort"	    =>	"int",
        "status"	=>	"int",
        "login_ip"	=>	"string",
        "create_time"	=>	"int",
        "update_time"	=>	"int",
    ];

    /**
     * 管理员关系角色
     */
    public function adminRole():\think\model\relation\HasOne
    {
        return $this->hasOne(AdminRole::class,'admin_id','id');
    }

    /**
     * 新增数据
     * @param array $data
     * @return bool
     */
    public function addData(array $data) : bool
    {
        $this->startTrans();
        try {
            $result = $this->create($data);
            $roleData = [
                'admin_id' =>  $result->id,
                'role_id'  =>  $data['role_id']
            ];
            // 新增前查询是否存在
            $adminRoleData = AdminRole::where($roleData)->select()->toArray();
            if (!empty($adminRoleData)) {
                $roleData['id'] = $adminRoleData[0]['id'];
            }
            $result1 = $this->adminRole()->save($roleData);
            if ($result1) {
                $this->commit();
                return true;
            } else {
                $this->rollback();
                return false;
            }
        } catch (\Exception $e) {
            $this->rollback();
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 编辑数据
     * @param array $data
     * @return bool
     */
    public function editData(array $data) : bool
    {
        $this->startTrans();
        try {
            if (!isset($data['role_id'])) {
                return false;
            }

            if (isset($data['id']) && $data['id'] > 0) {
                $roleData = [
                    'admin_id' =>  $data['id'],
                    'role_id'  =>  $data['role_id']
                ];
                // 修改前查询是否存在角色ID
                $adminRoleData = AdminRole::where($roleData)->select()->toArray();
                if (!empty($adminRoleData)) {
                    $roleData['id'] = $adminRoleData[0]['id'];
                }
                $result = $this->find($data['id']);
                $result = $result->save($data);
                $result1 = $this->adminRole()->save($roleData);
                if ($result && $result1) {
                    $this->commit();
                    return true;
                } else {
                    $this->rollback();
                    return false;
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->rollback();
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }


    /**
     * 获取用户
     * @param array $field
     * @param array $data
     * @return array
     */
    public function getUserInfo(array $data,array $field = []) : array
    {
        if (empty($field)) {
            $this->field = [];
        } else {
            $this->field = $field;
        }
        try {
            $result = $this->field($this->field)->with('adminRole')->where($data)->find();
            if (isset($result['adminRole']) && !empty($result['adminRole'])) {
                $result['role_id'] = $result['adminRole']->toArray()['role_id'];
                unset($result['adminRole']);
            } else {
                $result['role_id'] = 0;
            }
            return $result ? $result->toArray() : [];
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }
    }

    /**
     * 根据当前用户角色id获取角色对应的权限
     * @param array $data
     * @return array
     */
    public function getUserMenuList(array $data):array
    {
        return (new Menu())->routersByTree($data['role_id']);
    }


}

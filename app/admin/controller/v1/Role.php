<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ExceptionLog;
use app\admin\model\Role as RoleModel;
use app\admin\validate\Role as RoleValidate;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 角色组控制器
 */
class Role extends Base
{
    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function initialize():void
    {
        parent::initialize();
        $this->model = new RoleModel();
        $this->validate = new RoleValidate();
        // 详情字段
        $this->infoField = ['id','name','value','remark','menu','pid','status'];
        // 新增、编辑增加字段
        if (isVarExists($this->inputData,'menu')) {
            $this->editField['menu'] = implode(',',$this->inputData['menu']);
            $this->addField['menu'] = implode(',',$this->inputData['menu']);
        }
        // 快速搜索，匹配多个字段
        if (isVarExists($this->inputData,'quick_search')) {
            $this->vague = array_merge($this->vague, array_fill_keys(['name','value','remark'],$this->inputData['quick_search']));
        }

        // 删除方法检测数据有没有被引用,先查看有没有下级分类，再查当前角色有没有被其他用户引用
        if ($this->request->method() == 'DELETE') {
            if (isVarExists($this->inputData,'id')) {
                $this->isDeleteUsed = $this->model->getUsedDataById((int)$this->inputData['id']);
            }
        }
    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response\Json
    {
        try {
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            //判断是否需要分页 特殊情况，不需要分页
            if (isset($this->inputData['page']) && $this->inputData['page'] != 0) {
                unset($this->inputData['page']);
            }

            if (isset($this->inputData['size']) && $this->inputData['size'] != 0) {
                unset($this->inputData['size']);
            }
            // 列表输出字段
            if (isset($this->indexField) && !empty($this->indexField)) {
                $this->field = $this->indexField;
            }
            $result = $this->model->getIndexList($this->page,$this->size,$this->field,$this->vague,$this->focus,$this->order,$this->range);

            if (isset($this->inputData['isTree']) && $this->inputData['isTree'] == 'true') {
                $newData = assembleTree(getTreeRemark(tree($result)));
            } else {
                // 组装数据列表数据
                foreach ($result as $key => $item) {
                    if (isVarExists($item,'menu')) {
                        if ($item['menu'] == '*') {
                            $result[$key]['menu'] = '超级管理员';
                        } else {
                            // 取第一个权限
                            $authIds = explode(',',$item['menu']);
                            $menuName = $this->model->getMenuNameById($authIds[0]);
                            $result[$key]['menu'] = $menuName.'等'.count($authIds).'项';
                        }
                    }
                }


                $newData = [
                    'group' =>  [$this->adminInfo['role_id']],
                    'list'  =>  tree($result)
                ];
            }

            $this->sql = $this->model->getLastSql();
            //构建返回数据结构
            return $this->jr('获取成功',!empty($newData) ? $newData : []);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

}

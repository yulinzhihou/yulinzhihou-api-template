<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ExceptionLog;
use app\admin\model\Menu as MenuModel;
use app\admin\validate\Menu as MenuValidate;

/**
 * 菜单控制器
 */
class Menu extends Base
{
    public function initialize():void
    {
        parent::initialize();
        $this->model = new MenuModel();
        $this->validate = new MenuValidate();
        // 编辑字段
        $this->editExpectField = ['create_time','update_time'];
    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response\Json
    {
        try {
            $result = $this->model->routersByTree($this->adminInfo['role_id']);

            if (isset($this->inputData['isTree']) && $this->inputData['isTree'] == 'true') {
                $newData = assembleTree(getTreeRemark($result,'title'));
            } else {
                $newData = $result;
            }

            $this->sql = $this->model->getLastSql();
            //构建返回数据结构
            return $this->jr('获取成功',!empty($newData) ? $newData : []);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }


    /**
     * 权限按钮
     */
    public function buttons() :\think\Response\Json
    {
        $result = $this->model->getAuthButtons($this->adminInfo['role_id']);
        if (!empty($result)) {
            //构建返回数据结构
            return $this->jr('获取成功',$result);
        }
        //构建返回数据结构
        return $this->jr('获取失败');
    }
}

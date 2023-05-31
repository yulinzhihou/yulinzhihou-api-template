<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Admin as AdminModel;
use app\admin\model\ExceptionLog;
use app\admin\validate\Admin as AdminValidate;
use think\facade\Config;
use think\helper\Str;

/**
 * 后台管理员类
 */
class Admin extends Base
{
    public function initialize():void
    {
        parent::initialize();
        $this->model = new AdminModel();
        $this->validate = new AdminValidate();
        // 列表字段
        $this->indexField = ['id','username','nickname','phone','avatar','desc','phone','email','status','sort','login_ip','login_time','create_time'];
        // 详情字段
        $this->infoField = ['id','username','nickname','role_id','phone','avatar','desc','phone','email','status'];
        // 快速搜索，匹配多个字段
        if (isVarExists($this->inputData,'quick_search')) {
            $this->vague = array_merge($this->vague, array_fill_keys(['username','nickname'],$this->inputData['quick_search']));
        }
    }

    /**
     * 后台管理员登录首页接口
     */
    public function getUserAuth() :\think\Response\Json
    {
        // 管理员权限菜单
        $menuList = $this->model->getUserMenuList($this->adminInfo);
        $data = [
            "adminInfo"     => $this->adminInfo,
            "menus"         => $menuList,
            //TODO::后期增加动态配置数据的读取调用
            "siteConfig"     => [
                "site_name" => "GS GMAdmin",
                "version"   => "v1.1.1",
                "cdn_url"   => Config::get('app.app_url'),
                "api_url"   => Config::get('app.app_url'),
                "upload"    => [
                    "maxsize"   => 10485760,
                    "savename"  => "/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}",
                    "mimetype"  => "jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,pdf,txt",
                    "mode"      => "local"
                ]
            ],
            //TODO::后期增加动态配置数据的读取调用
            "terminal"      => [
                "install_service_port"  => "8000",
                "npm_package_manager"   => "pnpm"
            ]
        ];
        return $this->jr('获取成功',$data);
    }

    /**
     * 显示指定的资源
     */
    public function read():\think\Response\Json
    {
        try {
            //前置拦截
            if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
                return $this->jr('请输入需要获取的id值');
            }
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 列表输出字段
            if (isset($this->infoField) && !empty($this->infoField)) {
                $this->field = $this->infoField;
            }
            $result = $this->model->getInfo((int)$this->inputData['id'],[],$this->field);
            $this->sql = $this->model->getLastSql();
            $result['password'] = '';
            return $this->jr(['获取失败','获取成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 保存新建的资源
     */
    public function save():\think\Response\Json
    {
        try {
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            $this->inputData['salt'] = Str::random(mt_rand(5,10));

            $this->inputData['password'] = password_hash($this->inputData['password'] . $this->inputData['salt'],PASSWORD_ARGON2I); // 加密密码，（与新增管理员加密方式一致）

            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            $result = $this->model->addData($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['新增失败','新增成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 保存更新的资源
     */
    public function update():\think\Response\Json
    {
        if (isVarExists($this->inputData,'password')) {
            $this->inputData['salt'] = Str::random(mt_rand(5,10));
            $this->inputData['password'] = password_hash($this->inputData['password'] . $this->inputData['salt'],PASSWORD_ARGON2I); // 加密密码，（与新增管理员加密方式一致）
        } else {
            unset($this->inputData['password']);
        }
        return parent::update();
    }


    /**
     * 新增用户拉取对应数据给前台
     */
    public function createUser():\think\Response\Json
    {
        // 获取菜单数据
        $menuList = $this->model->getMenuList($this->adminInfo);
        // 获取角色列表数据
        $roleList = $this->model->getRoleList();
        // 获取角色权限
        $roleAuth = $this->model->getRoleAuth($this->adminInfo);
        // 检查角色id
        return $this->jr('获取成功',[$menuList,$roleList,$roleAuth,1,[]]);
    }

    /**
     * 获取用户详情
     */
    public function userInfo():\think\Response\Json
    {
        $inputData = $this->request->param();
        if ($this->commonValidate(__FUNCTION__,$inputData)) {
            return $this->message(true);
        }
        $field = ['id','username','real_name','phone','email','avatar'];
        if (isset($this->adminInfo['id']) && !empty($this->adminInfo['id'])) {
            $result = $this->model->getUserInfo(['id' => $this->adminInfo['id']],$field);
            if (!empty($result)) {
                $result['roles'] = [$this->adminInfo['role_key']];
                $result['realName'] = $result['real_name'];
                unset($result['real_name']);
            }
            return $this->jr(['获取成功','获取失败'],$result);
        }
        return $this->jr('用户信息已经过期，请重新登录');
    }
}
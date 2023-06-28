<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;

use app\admin\model\Admin as AdminModel;
use app\admin\model\Role as RoleModel;
use app\library\JwtUtil;
use think\facade\Cache;
use app\admin\validate\Login as LoginValidate;
use think\facade\Config;
use think\facade\Session;

/**
 * 后台登录控制器
 */
class Login extends Base
{
    /**
     * 定义管理员模型
     */
    protected AdminModel $adminModel;

    /**
     * 角色模型
     */
    protected RoleModel $roleModel;

    /**
     * 初始化方法
     */
    public function initialize():void
    {
        parent::initialize();
        $this->adminModel = new AdminModel();
        $this->validate = new LoginValidate();
        $this->roleModel = new RoleModel();
    }

    /**
     * 测试接口-演示数据
     */
    public function index():\think\response\Json
    {
        $data = [
            "site" => [
                "site_name" => "yulinzhihou-template-api",
                "record_number" => "",
                "version" => "v1.0.0",
                "cdn_url" => "https://yulinzhihou-template-api.yulinzhihou.com",
                "upload" => [
                    "maxsize" => 10485760,
                    "save_name" => "/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}",
                    "mimetype" => "jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,pdf,txt",
                    "mode" => "local"
                ]
            ],
            "open_member_center" => ''
        ];
        return $this->jr('获取成功',$data);
    }

    /**
     * 登录
     */
    public function login(): \think\Response\Json
    {
        //额外增加请求参数, 演示数据暂时没有具体作用，可以给$this->params进行手动赋值，来实现暗箱操作
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        // 通用验证：调用验证器进行数据验证
        if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
            return $this->message(true);
        }

        // token 接口模式，如果是已经正常登录的用户
        // 因为中间件已经解析了 token 模式下的数据
        $token = $this->request->header('Authorization');
        if (count(explode('.',$token)) === 3) {
            $userInfo = $this->request->user_info;
            return $this->jr('欢迎回来！[token模式]',$userInfo['return_data']);
        }

        //获取用户信息
        $userInfo = $this->adminModel->getUserInfo(['username'=>$this->inputData['username']]);
        if (!empty($userInfo)) {
            if ($userInfo['status'] === 0) {
                return $this->jr('用户已经禁用');
            }
            $passRet = password_verify($this->inputData['password'].$userInfo['salt'],$userInfo['password']);
            if (!$passRet) {
                return $this->jr('用户密码不正确');
            }
            if ($userInfo['role_id'] === 0) {
                return $this->jr('用户角色不存在，请联系系统管理员');
            } else {
                // 角色是否为超级管理员
                $userInfo['super'] = $userInfo['role_id'] === 1;
            }

            // Session-Cookie 记录Session
            if (Session::has('admin_login_info') && Session::get('admin_login_info') != '') {
                $userData = Session::get('admin_login_info');
                return $this->jr('欢迎回来！[session-cookie模式]',$userData['return_data']);
            }

            // 以上验证都通过后对管理员签发登录证书
            // 获取角色组key
            $roleInfo = $this->roleModel->getInfo($userInfo['role_id'],['status'=>1]);

            if (empty($roleInfo)) {
                return $this->jr('角色信息不正确');
            }

            $RSAKey = Config::get('jwt.is_rsa') ?  Config::get('jwt.pri_key_path') : Config::get('jwt.app_key');
            $RSAKey = file_get_contents($RSAKey);
            // 调用jwt工具类中issue()方法，传入用户ID，模拟传入角色组关键词
            $jwt = JwtUtil::issue($userInfo['id'], $roleInfo['value'],$userInfo,$RSAKey);

            // 更新登录IP
            $updateData = [
                'id'            => $userInfo['id'],
                'login_ip'      => $this->request->ip(),
                'login_time'    => time(),
                'login_failure' => 0
            ];
            // 更新登录记录
            $this->adminModel->editData($updateData);
            // 记录接口最后一条sql记录
            $this->sql = $this->adminModel->getLastSql();
            // 返回给前端的接口数据，根据具体情况进行编辑
            $data = [
                'id'        => $userInfo['id'],
                'avatar'    => $userInfo['avatar'],
                'nickname'  => $userInfo['nickname'],
                'token'     => $jwt['token'],
                'login_time'    => $userInfo['update_time'],
                'refresh_token' => $jwt['refresh_token'],
                'super'     => $userInfo['super'],
                'username'  => $userInfo['username']
            ];
            // 缓存记录 接口登录成功记录缓存 用于白名单接口获取用户数据。
            Cache::set('admin_login_info:user_id-'.$userInfo['id'],array_merge($userInfo,['role_info' => $roleInfo,'return_data'=>$data]),3600);
            // Session-Cookie 记录Session
            Session::set('admin_login_info',array_merge($userInfo,['role_info' => $roleInfo,'return_data'=>$data]));

            // 返回登录token
            return $this->jr('登录成功',$data);
        } else {
            $this->sql = $this->adminModel->getLastSql();
            return $this->jr('用户不存在');
        }
    }

    /**
     * 单点注销，退出登录
     */
    public function logout(): \think\response\Json
    {
        $userId = $this->adminInfo['id'];
        if (Cache::has('admin_login_info:user_id-'.$userId) && Cache::get('admin_login_info:user_id-'.$userId) != '') {
            Cache::delete('admin_login_info:user_id-'.$userId);
        } elseif (Cache::has('admin_login_info') && Cache::get('admin_login_info') != '') {
            Session::delete('admin_login_info');
        }
        return $this->jr('退出登录成功',true);
    }
}
<?php
declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\library\JwtUtil;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Env;
use think\facade\Session;
use think\response\Json;

/**
 * 请求中间件,兼容jwt token 和 Session
 */
class checkSign
{
    /**
     * 处理请求
     * @return mixed|void
     */
    public function handle(\think\Request $request, \Closure $next)
    {
        //过滤OPTIONS请求
        $origin = $request->header('origin');
        $allowHeaders = [
            'Authorization',
            'Content-Type',
            'think-lang',
            'If-Match',
            'If-Modified-Since',
            'If-None-Match',
            'If-Unmodified-Since',
            'X-Requested-With',
            'x_requested_with',
            'server'
        ];
        if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Origin: ".$origin);
            header("Access-Control-Allow-Headers: ".implode(',',$allowHeaders));
            header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            exit;
        }
        header("Access-Control-Allow-Origin: ".$origin);
        header("Access-Control-Allow-Headers: ".implode(',',$allowHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');

        // 路由白名单
        $whitelist = Config::get('whitelist');
        $route = strtolower(request()->pathinfo());
        //判断是否是 token 或者是 Session-Cookie 通信模式
        if (Session::has('admin_login_info') && Session::get('admin_login_info') != '') {
            // 表示是Session-Cookie 通讯
            $userInfo = Session::get('admin_login_info');
            // 传递用户信息给请求
            $request->user_info = $userInfo;
        } elseif ($request->header('authorization') != '') {
            // Token方式
            // 前端请求携带的Token信息，根据请求头字段
            $token = $request->header('authorization');
            // 对登录控制器放行
            if (!in_array($route, $whitelist)) {
                if (empty($token) || $token == '') {
                    return $this->doReturn('携带token不合法。请确认请求的 token 否正常！',1,599);
                }

                $isRas = Env::get('jwt.is_rsa',false);
                $key = $isRas ? root_path().Env::get('jwt.cert_path').DIRECTORY_SEPARATOR.Env::get('jwt.name').'.pem' : Env::get('jwt.app_key');
                $key = file_get_contents($key);
                $jwtInfo = JwtUtil::verification($key, $token,$isRas ? 'RS256' : 'HS256'); // 与签发的key一致
                if ($jwtInfo['status'] == 200) {
                    if (Cache::has('admin_login_info:user_id-'.$jwtInfo['data']['data']['uid']) && Cache::get('admin_login_info:user_id-'.$jwtInfo['data']['data']['uid']) != '') {
                        // 传递用户信息给请求
                        $request->user_info = $jwtInfo['data']['data']['user_info'];
                    } else {
                        return $this->doReturn('退出系统成功',0,599);
                    }

                } else {
                    return $this->doReturn($jwtInfo['message'],1,599);
                }
            }
            // 访问白名单
        } elseif (in_array($route, $whitelist,true)) {
            // 为了防止特殊接口请求，目前这里的设计是需要提供特殊密钥验证，比如随机生成一个字符串，进行请求头的传递。
//            $sn = strtolower($request->header('x-token'));
//            if ($sn != '' && $sn != Env::get('YF_MANUAL_SN')) {
//                return $this->doReturn('请联系后台接口，提供手动密钥字符串密钥');
//            }
            //TODO:: 暂时不做处理。后期再加验证规则
            return $next($request);
        } else {
            return $this->doReturn('未登录系统,或已经退出系统',1,599);
        }

        return $next($request);
    }


    /**
     * 通用返回
     * @param string $msg
     * @param int $type
     * @param int $code
     * @return Json
     */
    private function doReturn(string $msg,int $type = 1,int $code = 504):\think\response\Json
    {
        $data = [
            'status'        => $type == 1 ? $code == 504 ? : $code : 200,
            'code'          => $type,
            'data'          => [],
            'message'       => $msg,
            'type'          => $type == 1 ? 'ERROR' : 'SUCCESS',
            'time'          => time(),
            'date'          => date('Y-m-d H:i:s',time())
        ];
        return json($data,$data['status']);
    }
}
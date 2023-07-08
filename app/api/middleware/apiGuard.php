<?php
declare(strict_types=1);
namespace app\api\middleware;

use app\library\JwtUtil;
use think\facade\Cache;
use think\facade\Config;
use think\response\Json;

/**
 * API 接口层中间件
 */
class apiGuard
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
            'If-Match',
            'If-Modified-Since',
            'If-None-Match',
            'If-Unmodified-Since'
        ];
        header("Access-Control-Allow-Origin: ".$origin);
        header("Access-Control-Allow-Headers: ".implode(',',$allowHeaders));
        header('Access-Control-Allow-Credentials: true');
        $method = 'Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE';
        if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            header($method.' OPTIONS');
            exit;
        }
        header($method);

        // 路由白名单
        $whitelist = Config::get('nocheck');
        $route = strtolower(request()->pathinfo());
        // Jwt token
        $jwtToken = $request->header('Authorization');

        //判断是否是 token 或者是 Session-Cookie 通信模式
        if ($jwtToken != '' && \count(explode('.',$jwtToken)) === 3) {
            // Token方式
            // 前端请求携带的Token信息，根据请求头字段
            $token = $request->header('Authorization');
            // 对登录控制器放行
            $isRas = Config::get('jwt.is_rsa');
            $key = $isRas ? Config::get('jwt.pub_key_path') : Config::get('jwt.app_key');
            $key = file_get_contents($key);
            // 与签发的key一致
            $jwtInfo = JwtUtil::verification($key, $token,$isRas ? 'RS256' : 'HS256');

            if ($jwtInfo['status'] == 200) {
                // TODO::应用授权APP
            }
            // 访问白名单
        } elseif (in_array($route, $whitelist,true)) {
            // 为了防止特殊接口请求，目前这里的设计是需要提供特殊密钥验证，比如随机生成一个字符串，进行请求头的传递。
//            $sn = strtolower($request->header('x-token'));
//            if ($sn != '' && $sn != Env::get('YF_MANUAL_SN')) {
//                return $this->doReturn('请联系后台接口，提供手动密钥字符串密钥');
//            }
            //TODO:: 暂时不做处理。后期再加验证规则，为防止渗透测试，建议增加token走token流程。
            return $next($request);
        } else {
            return $this->doReturn('未登录系统,或已经退出系统',1,599);
        }

        return $next($request);
    }

    /**
     * 自动续约 token, 理论上每进来一次接口请求，相当于就应该刷新token的时间，
     * 比如token理论上是1个小时，现在第一次接口请求的时候是登录接口的第2秒，那这个时候，token过期时间应该重新激活，
     * 但这样就会有一个问题，token会无限的刷新和使用，相当于每次请求，都会是一个不同的token。这样好像有点不太礼貌
     *
     * @param array $tokenData
     * @return array
     */
    private function refreshToken(array $tokenData):array
    {
        //TODO:: 暂时不自动无脑生成吧，让调用者，前端进行刷新TOKEN的逻辑与接口完成
        return [];
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
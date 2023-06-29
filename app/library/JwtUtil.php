<?php
declare (strict_types=1);

namespace app\library;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Cache;
use think\facade\Config;

/**
 * JWT封装类
 */
class JwtUtil
{
    /**
     * 证书签发
     * @param string $RSAKey
     * @param int $uid 用户id
     * @param string $role_key 角色组key
     * @param array $userInfo 用户登录信息
     * @return array
     */
    public static function issue(int $uid, string $role_key, array $userInfo, string $RSAKey): array
    {
        // 签名密钥
        $key = Config::get('jwt.app_key','yulinzhihou-template-api');
        $tokenData = [
            'iss' => Config::get('jwt.iss', 'apiy.test'),   // 签发者
            'aud' => Config::get('jwt.aud', 'apiy.test'),   // 接收方
            'iat' => time(), // 签发时间
            'nbf' => time(), // 签名生效时间
            'exp' => time() + Config::get('jwt.exp', 7200), // 签名有效时间（3600 * x）x小时
            'data' => [                 /*用户信息*/
                'uid' => $uid,     /*用户ID*/
                'role' => $role_key,/*用户角色组key*/
                'user_info' => $userInfo /*用户登录信息*/
            ]
        ];
        // 根据token签发证书
        $token = JWT::encode($tokenData, Config::get('jwt.is_rsa', false) ? $RSAKey : $key, Config::get('jwt.is_rsa', false) ? 'RS256' : 'HS256');
        // refresh Token
        $tokenData['exp'] = time() + 7 * 86400;
        // 生成 refresh token
        $refreshToken = JWT::encode($tokenData, Config::get('jwt.is_rsa', false) ? $RSAKey : $key, Config::get('jwt.is_rsa', false) ? 'RS256' : 'HS256');

        return ['token' => $token,'refresh_token'=>$refreshToken];
    }

    /**
     * 根据 refresh-token 刷新获取新的 token，返回新的 token 和 refresh-token
     * @param string $refreshToken
     * @return array
     */
    public static function refreshToken(string $refreshToken):array
    {
        // 首先拿到 refresh-token 进行检测
        $isRas = Config::get('jwt.is_rsa');
        $key = $isRas ? Config::get('jwt.pri_key_path') : Config::get('jwt.app_key');
        $key = file_get_contents($key);
        $jwtInfo = JwtUtil::verification($key, $refreshToken,$isRas ? 'RS256' : 'HS256'); // 与签发的key一致
        if ($jwtInfo['status'] == 200) {
            $uid = $jwtInfo['data']['uid'];
            $role_key = $jwtInfo['data']['role'];
            $userInfo = $jwtInfo['data']['user_info'];
            // 直接调用生成
            $returnData = self::issue($uid,$role_key,$userInfo,$key);
            return [
                'status'    => 200,
                'message'   => 'success',
                'data'      => $returnData
            ];
        } else {
            return ['status' => 504,'message' => 'refresh_token 异常'];
        }
    }

    /**
     * 解析签名，按issue中的token格式返回
     * @param $key
     * @param $jwt
     * @param string $alg
     * @return array
     */
    public static function verification($key, $jwt, string $alg = 'RS256'): array
    {
        try {
            JWT::$leeway = 60;  // 当前时间减去60， 时间回旋余地
            $resultData = JWT::decode($jwt, new Key($key, $alg));  // 解析证书
            $resultData = json_decode(json_encode($resultData), true);
            return ['status' => 200, 'message' => 'success', 'data' => $resultData];
        } catch (SignatureInvalidException $e) {   // 签名不正确
            return ['status' => 599, 'message' => 'token 签名不正确', 'data' => []];
        } catch (BeforeValidException $e) {        // 当前签名还不能使用，和签发时生效时间对应
            return ['status' => 599, 'message' => '当前签名还不能使用，和签发时生效时间对应', 'data' => []];
        } catch (ExpiredException $e) {            // 签名已过期
            return ['status' => 599, 'message' => 'token 已过期', 'data' => []];
        } catch (\Exception $e) {                  // 其他错误
            return ['status' => 599, 'message' => 'token 异常', 'data' => []];
        }
    }
}

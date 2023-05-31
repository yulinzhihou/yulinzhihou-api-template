<?php
declare (strict_types=1);

namespace app\library;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Env;

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
     * @return string
     */
    public static function issue(int $uid, string $role_key, array $userInfo, string $RSAKey): string
    {
        $key = Env::get('app_key', 'test');         // 签名密钥
        $token = [
            'iss' => Env::get('jwt.iss', 'local'),   // 签发者
            'aud' => Env::get('jwt.aud', 'test'),   // 接收方
            'iat' => time(), // 签发时间
            'nbf' => time(), // 签名生效时间
            'exp' => time() + Env::get('jwt.exp', 7200), // 签名有效时间（3600 * x）x小时
            'data' => [                 /*用户信息*/
                'uid' => $uid,     /*用户ID*/
                'role' => $role_key,/*用户角色组key*/
                'user_info' => $userInfo /*用户登录信息*/
            ]
        ];

        // 根据token签发证书
        return JWT::encode($token, Env::get('jwt.is_rsa', false) ? $RSAKey : $key, Env::get('jwt.is_rsa', false) ? 'RS256' : 'HS256');
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
            return ['status' => 599, 'message' => $e->getMessage(), 'data' => []];
        } catch (BeforeValidException $e) {        // 当前签名还不能使用，和签发时生效时间对应
            return ['status' => 599, 'message' => $e->getMessage(), 'data' => []];
        } catch (ExpiredException $e) {            // 签名已过期
            return ['status' => 599, 'message' => $e->getMessage(), 'data' => []];
        } catch (\Exception $e) {                  // 其他错误
            return ['status' => 599, 'message' => $e->getMessage(), 'data' => []];
        }
    }
}

<?php
// +----------------------------------------------------------------------
// | jwt 配置文件
// +----------------------------------------------------------------------

use think\facade\Env;

return [
//    [JWT]
//NAME=yulinzhihou-api-template
//APP_KEY=3EnyhStklmwU1TPzNcp50g8XQu7DOdj6
//IS_RSA=true
//CERT_PATH=certs
//ISS=apiy.test
//AUD=apiy.test
//EXP=3600
    // jwt 名称标识
    'name'          => Env::get('jwt.name','yulinzhihou-template-api'),
    // jwt 用于签名的密钥
    'app_key'       => Env::get('jwt.app_key',''),
    // 是否启用 rsa 非对称加密，默认：true
    'is_rsa'        => Env::get('jwt.is_rsa',true),
    // 存储RSA证书的位置
    'cert_path'     => Env::get('jwt.cert_path','certs'),
    // jwt 签发组织
    'iss'           => Env::get('jwt.iss',''),
    // jwt 认证组织
    'aud'           => Env::get('jwt.aud',''),
    // jwt 公钥位置
    'pub_key_path'  => root_path().Env::get('jwt.cert_path','certs').DIRECTORY_SEPARATOR.Env::get('jwt.name','yulinzhihou-template-api').'.pem',
    // jwt 私钥位置
    'pri_key_path'  => root_path().Env::get('jwt.cert_path','certs').DIRECTORY_SEPARATOR.Env::get('jwt.name','yulinzhihou-template-api').'.key',
];

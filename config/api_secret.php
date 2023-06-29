<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | api接口传输是否加密配置文件
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // jwt 名称标识
    'is_admin_secret'   => Env::get('api_secret.is_admin_secret', false),
    // jwt 用于签名的密钥
    'is_api_secret'     => Env::get('api_secret.is_api_secret', false),
    // 是否启用 rsa 非对称加密，默认：true
    'is_index_secret'   => Env::get('api_secret.is_index_secret', true),
    // 存储RSA证书的位置
    'is_secret_chunk'   => Env::get('api_secret.is_secret_chunk', false),
];
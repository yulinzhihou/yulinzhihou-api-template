<?php
declare(strict_types=1);

/**
 * 一些思考：
 * 1、服务端私钥加密（encryptByPrivateKey），客户端公钥解密 --- 适用于服务端接口输出给客户端。公钥存在于客户端
 * 2、服务端公钥加密（encryptByPublicKey)）, 服务端私钥解密，一般用于服务器与服务器之间的接口解密。
 * 3、服务端接收数据解密（decryptByPublicKey),客户端公钥加密，正常的 web 端请求接口，如果请求过来的数据也需要加密的话。
 * 4、服务端接口数据解密（decryptByPrivateKey),另外一个服务端使用私钥加密数据请求传到这边来。感觉这种情况应该比较少见吧。
 *
 * 总之：私钥不能放在用户随便就能拿到的地方，具体情况就看应用场景，这里面只是给出了几类方案
 */

namespace app\library;

use ParagonIE\EasyRSA\EasyRSA;
use ParagonIE\EasyRSA\Exception\InvalidChecksumException;
use ParagonIE\EasyRSA\Exception\InvalidCiphertextException;
use ParagonIE\EasyRSA\PrivateKey;
use ParagonIE\EasyRSA\PublicKey;
use think\facade\Config;

/**
 * 非对称加密类。专职加密解密接口数据，不带感情
 * 为了接口数据安全，我们拼了。
 * 为此：服务端加密，客户端同样拿到数据需要解密才能用。
 * 与些同时，前端数据传输也需要加密传输，防止被别人拿到接口的参数以及地址
 */
class RsaEncrypt
{
    /**
     * Rsa 公钥地址
     * @var string
     */
    protected static string $publicKeyPath;

    /**
     * Rsa 私钥地址
     * @var string
     */
    protected static string $privateKeyPath;

    /**
     * Rsa 公钥
     * @var string
     */
    protected static string $publicKey;

    /**
     * Rsa 私钥
     * @var string
     */
    protected static string $privateKey;

    /**
     * 是否分段分块加密解密数据
     * @var bool
     */
    protected static bool $isSecretChunk;

    /**
     * 单例
     * @var null
     */
    protected static $RsaEncrypt;

    /**
     * 构造方法，初始化配置文件及密钥值
     */
    private function __construct()
    {
        self::$privateKeyPath = Config::get('jwt.pri_key_path');
        self::$publicKeyPath = Config::get('jwt.pub_key_path');
        self::$privateKey = file_get_contents(self::$privateKeyPath);
        self::$publicKey = file_get_contents(self::$publicKeyPath);
        self::$isSecretChunk = Config::get('api_secret.is_secret_chunk');
    }

    /**
     *
     * @return $this
     */
    public static function instance():self
    {
        if (!self::$RsaEncrypt) {
            self::$RsaEncrypt = new self();
        }
        return self::$RsaEncrypt;
    }

    /**
     * 加密算法-用公钥进行加密
     * @desc 适合拥有私钥的服务端程序拿私钥进行解密
     * @param mixed $data 需要加密的数据
     * @return array
     */
    public static function encryptByPublicKey(mixed $data): array
    {
        // 前置处理方法
        $plainText = self::dataToChunkEncrypt($data);
        // 通过方法处理，得到了一串可能是分段或者不分段的数据
        $publicKey = new PublicKey(self::$publicKey);
        if (self::$isSecretChunk) {
            // 分块加密再组装
            $plainTextArr = explode('.',$plainText);
            foreach ($plainTextArr as &$value) {
                $value = EasyRSA::encrypt($value,$publicKey);
            }
            $encryptData = implode('.',$plainTextArr);
        } else {
            // 直接加密
            $encryptData = EasyRSA::encrypt($plainText,$publicKey);
        }
        return ['data' => $encryptData];
    }

    /**
     * 加密算法-数据分块公钥加密
     * 适合拥有私钥的服务端程序拿私钥进行解密
     * @param mixed $data 需要加密的数据
     * @return array
     */
    public static function encryptByPublicKeyChunk(mixed $data):array
    {
        self::$isSecretChunk = true;
        return self::encryptByPublicKey($data);
    }

    /**
     * 加密算法-私钥加密
     * 适合拥有对应公钥的客户端进行解密拿数据。
     * @param mixed $data
     * @return array
     */
    public static function encryptByPrivateKey(mixed $data):array
    {
        // 前置处理方法
        $plainText = self::dataToChunkEncrypt($data);
        // 通过方法处理，得到了一串可能是分段或者不分段的数据
        if (self::$isSecretChunk) {
            // 分块加密再组装
            $plainTextArr = explode('.',$plainText);
            foreach ($plainTextArr as &$value) {
                \openssl_private_encrypt($value,$value,self::$privateKey);
            }
            $encryptData = implode('.',$plainTextArr);
        } else {
            // 直接加密
            \openssl_private_encrypt($plainText,$encryptData,self::$privateKey);
        }
        return ['data' => $encryptData];
    }

    /**
     * 加密算法-数据分块私钥加密
     * 适合拥有对应公钥的客户端进行解密拿数据。
     * @param mixed $data
     * @return array
     */
    public static function encryptByPrivateKeyChunk(mixed $data):array
    {
        self::$isSecretChunk = true;
        return self::encryptByPrivateKey($data);
    }

    /**
     * 解密算法-用公钥进行解密数据
     * 适合：服务端生成加密数据输出给API调用
     * @param mixed $data 需要解密的数据
     * @return array
     */
    public static function decryptByPublicKey(mixed $data):array
    {
        if (self::$isSecretChunk) {
            // 分块加密再组装
            $plainTextArr = explode('.',$data);
            foreach ($plainTextArr as &$value) {
                 \openssl_public_decrypt($value,$value,self::$publicKey);
            }
            $encryptData = implode('.',$plainTextArr);
        } else {
            // 直接解密
            \openssl_public_decrypt($data,$encryptData,self::$publicKey);
        }
        // 后置处理方法
        return self::dataToChunkDecrypt($encryptData);
    }

    /**
     * 解密算法-数据分块用公钥进行解密数据
     * @param mixed $data 需要解密的数据
     * @return array
     */
    public static function decryptByPublicKeyChunk(mixed $data):array
    {
        self::$isSecretChunk = true;
        return self::decryptByPublicKey($data);
    }

    /**
     * 解密算法-用私钥进行解密数据
     * 适合：服务端生成加密数据输出给API调用
     * @param mixed $data 需要解密的数据
     * @return array
     * @throws InvalidChecksumException
     * @throws InvalidCiphertextException
     */
    public static function decryptByPrivateKey(mixed $data):array
    {
        // 通过方法处理，得到了一串可能是分段或者不分段的数据
        $privateKey = new PrivateKey(self::$privateKey);

        if (self::$isSecretChunk) {
            // 分块加密再组装
            $plainTextArr = explode('.',$data);
            foreach ($plainTextArr as &$value) {
                $value = EasyRSA::decrypt($value,$privateKey);
            }
            $encryptData = implode('.',$plainTextArr);
        } else {
            // 直接解密
            $encryptData = EasyRSA::decrypt($data,$privateKey);
        }
        // 后置处理方法
        return self::dataToChunkDecrypt($encryptData);
    }

    /**
     * 解密算法-数据分块用私钥进行解密数据
     * @param mixed $data 需要解密的数据
     * @return array
     */
    public static function decryptByPrivateKeyChunk(mixed $data):array
    {
        self::$isSecretChunk = true;
        $chunkDecrypt = self::encryptByPrivateKey($data);
        return [$chunkDecrypt];
    }

    /**
     * 数据分块处理
     * @param mixed $data 处理需要加密的数据
     * @return string
     */
    private static function dataToChunkEncrypt(mixed $data):string
    {
        $newData = '';
        if (!empty($data)) {
            if (is_array($data)) {
                // 判断是否需要分块加密
                $newData = base64_encode(json_encode($data));
                if (self::$isSecretChunk) {
                    // 分块切片，这里又能让前端调用语言能更好的兼容，先使用base64进行数据加密，然后固定大小切块。根据数据的多少，进行动态的切割。
                    // 将其伪造成 jwt token 的模样。用3个 . 进行切割和拆分
                    // 不确定数据长短，比如 [0] 这种数组传过来。处理后的字符串才4个。分块出来不就是 e.y.x.z ？
                    // 所以只能进行动态分割，后面解密的时候，以点 . 进行拆散，组装，再解密2次。为了安全，真心不容易啊，效率可能也会降低，先实现功能吧，优化交给硬件
                    $len = strlen($newData);
                    $limit = (int)ceil($len / 3);
                    if ($limit > 0) {
                        $newData = implode('.',str_split($newData,$limit));
                    }
                }
            } else{
                // 属于非数组类数据。字符串，数值，布尔等
                // 这种用法在服务器端可能比较多，与客户端通讯基本上都会是以数组的形势存在
                $newData = base64_encode(json_encode([$data]));
            }
        }
        return $newData;
    }

    /**
     * 解密数据
     * @param string $data
     * @return array
     */
    private static function dataToChunkDecrypt(string $data):array
    {
        return json_decode(base64_decode($data),true);
    }
}
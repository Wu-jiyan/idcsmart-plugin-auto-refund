<?php
namespace addons\auto_refund\lib;

/**
 * 加密工具类
 * 兼容 PHP 7.2 - 7.4
 */
class EncryptUtil
{
    /**
     * 获取加密密钥
     * @return string
     */
    private static function getKey()
    {
        // 使用系统配置的密钥，如果不存在则生成一个
        $key = config('database.password');
        if (empty($key)) {
            $key = 'auto_refund_default_key_2024';
        }
        // 确保密钥长度为32字节（256位）
        return hash('sha256', $key, true);
    }

    /**
     * 对称加密
     * @param string $data 要加密的数据
     * @return string 加密后的数据（base64编码）
     */
    public static function encrypt($data)
    {
        if (empty($data)) {
            return '';
        }

        $key = self::getKey();
        $iv = openssl_random_pseudo_bytes(16);
        
        // 使用 AES-256-CBC 加密
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($encrypted === false) {
            return '';
        }

        // 将 IV 和加密数据拼接，然后进行 base64 编码
        return base64_encode($iv . $encrypted);
    }

    /**
     * 对称解密
     * @param string $data 加密后的数据（base64编码）
     * @return string 解密后的原始数据
     */
    public static function decrypt($data)
    {
        if (empty($data)) {
            return '';
        }

        $key = self::getKey();
        $data = base64_decode($data);
        
        if ($data === false || strlen($data) < 16) {
            return '';
        }

        // 提取 IV 和加密数据
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        // 解密
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            return '';
        }

        return $decrypted;
    }
}

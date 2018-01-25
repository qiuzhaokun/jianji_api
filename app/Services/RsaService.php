<?php
/**
 * Des: 非对称加密算法类
 * Author: larry
 * Date: 23/01/2018
 * Time: 10:47 AM
 */

namespace App\Services;

use App\Models\AppAccess;

class RsaService
{
    const CHAR_SET = "UTF-8";
    const BASE_64_FORMAT = "UrlSafeNoPadding";
    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA256;

    private static $keyLen = 1024;

    /**
     * 初始化 rsa 生成 public 和 private key
     * @param int $bitLen
     * @return array
     */
    public static function createKeys()
    {
        $res = openssl_pkey_new(['private_key_bits' => self::$keyLen]);
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        return ['public_key' => $public_key, 'private_key' => $private_key];
    }


    /**
     * 获取私钥
     * @return bool|resource
     */
    private static function getPrivateKey()
    {
        $privateKey = AppAccess::getAppInfoById(Env::getAppId(), 'private_key');
        return openssl_pkey_get_private($privateKey);
    }


    /**
     * 获取公钥
     * @return bool|resource
     */
    public static function getPublicKey()
    {
        $publicKey = AppAccess::getAppInfoById(Env::getAppId(), 'public_key');
        return openssl_pkey_get_public($publicKey);
    }


    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privateEncrypt($data = null)
    {
        if (empty($data)) {
            return null;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $encrypted = '';
        $part_len = self::$keyLen / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_private_encrypt($part, $encrypted_temp, self::getPrivateKey());
            $encrypted .= $encrypted_temp;
        }
        return url_safe_base64_encode($encrypted);
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public static function publicEncrypt(string $data)
    {
        if (empty($data)) {
            return null;
        }

        $encrypted = '';
        $part_len = self::$keyLen / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, self::getPublicKey());
            $encrypted .= $encrypted_temp;
        }

        return base64_encode($encrypted);
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public static function privateDecrypt(string $encrypted = null)
    {
        if (empty($encrypted)) {
            return null;
        }
        $decrypted = "";
        $part_len = self::$keyLen / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);
        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_private_decrypt($part, $decrypted_temp, self::getPrivateKey());
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt(string $encrypted = null)
    {
        if (empty($encrypted)) {
            return null;
        }

        $decrypted = "";
        $part_len = self::$keyLen / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);
        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_public_decrypt($part, $decrypted_temp, self::getPublicKey());
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /**
     * 对数据进行签名
     * @param $data
     * @return mixed|null
     */
    public static function sign($data)
    {
        if (empty($data)) {
            return null;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
        openssl_sign($data, $sign, self::getPrivateKey(), self::RSA_ALGORITHM_SIGN);

        return url_safe_base64_encode($sign);
    }

    /**
     * 验证数据签名正确性
     * @param $data
     * @param $sign
     * @return int|null
     */
    public static function verify($data, $sign)
    {
        if (empty($data)) {
            return null;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
        return openssl_verify($data, url_safe_base64_decode($sign), self::getPublicKey(), self::RSA_ALGORITHM_SIGN);
    }

}
<?php
/**
 *
 * Created by: larry
 * DateTime: 25/12/2017 15:32
 */

namespace App\Services;

class Aes extends BaseService
{
    public static function getPostArgs(&$request)
    {
        $encrypt = $request->input('encrypt');
        $decrypt = self::decrypt($encrypt);
        if (!$decrypt) {
            echo json_encode(['status' => 402, 'msg' => '请求失败，请重试！', 'data' => ['info' => 'aes decrypt']]);
            exit;
        }
        parse_str($decrypt, $result);
        //解密结果合并到 $request
        $request->merge($result);
        return true;
    }

    public static function decrypt($encrypt)
    {
        $key_arr = self::getAesKey();
        $iv = $key_arr['iv'];
        $sKey = $key_arr['key'];
        return openssl_decrypt(self::hexToStr($encrypt), 'AES-256-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
    }

    public static function getAesKey()
    {
        $appId = Env::getAppId();
        $timestamp = Env::getTimeStamp();
        $appInfo = App::findByAppId_cache($appId);
        $secret = $appInfo['secret'];
        $clientId = $appInfo['client_id'];
        $key = substr($secret, 12, 6) . substr($timestamp, 3, 4) . substr($secret, 22, 6) . substr($timestamp, 5, 4);
        $key .= substr($clientId, $timestamp % 10, 6);
        $md5 = strtoupper(md5($secret . $key));
        return ['key' => $md5, 'iv' => substr($md5, 16)];
    }

    public static function hexToStr($hex)
    {
        $bin = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $bin .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $bin;
    }

    public static function encrypt($input)
    {
        $key_arr = self::getAesKey();
        $iv = $key_arr['iv'];
        $sKey = $key_arr['key'];
        $res = openssl_encrypt(json_encode($input), 'AES-256-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
        return bin2hex($res);
    }

}
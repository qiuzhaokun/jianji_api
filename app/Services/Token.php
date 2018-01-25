<?php
/**
 *
 * Created by: larry
 * DateTime: 19/12/2017 15:23
 */

namespace App\Services;


use Carbon\Carbon;

class Token extends BaseService
{
    public static $current_token = '';
    private static $prefix = [
        'token'         => 'access_',
        'refresh_token' => 'access_f_',
    ];

    private static $allow_key = [
        'token',
        'mobile',
        'user_id',
        'open_id',
        'secret'
    ];

    public static function initToken($request, $app_info)
    {
        $uin = $request->uin;
        $current_time = Carbon::now()->timestamp;
        $cache_key = 'cache_token_' . $uin;
        $cache = Helper::getCache($cache_key);
        if (!empty($cache['token'])) {
            $token = self::getTokenData($cache['token']);
            if ($token) {
                $response = [
                    'access_token'  => $token['access_token'],
                    'refresh_token' => $token['refresh_token'],
                    'expire_in'     => $token['expire_in'] - $current_time,
                ];
                return $response;
            }
        }

        $access_token = Helper::uuid();
        $refresh_token = Helper::uuid();

        $expire_in = 7200;

        $access_token_data = [
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'app_id'        => $app_info['app_id'],
            'secret'        => $app_info['secret'],
            'user_id'       => '',
            'mobile'        => '',
            'created_at'    => $current_time,
            'expire_in'     => $current_time + $expire_in,
            'client_ip'     => Helper::getIp(),
        ];

        Helper::jsonSet(self::$prefix['token'] . $access_token, $access_token_data, $expire_in);

        #refresh_token 暂时不启用
        //Helper::jsonSet(self::$prefix['refresh_token'] . $access_token, $access_token_data, $expire_in * 12 * 7);

        Helper::setCache($cache_key, ['token' => $access_token], $expire_in - 10);

        $response = [
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'expire_in'     => $expire_in,
        ];
        return $response;
    }


    public static function getTokenData(string $token = null)
    {
        if (empty($token)) {
            $token = self::$current_token;
        }
        return Helper::jsonGet('access_' . $token);
    }

    public static function updateTokenData(array $data, string $token = null)
    {
        if (!$data) {
            return false;
        }
        $token = $token ?? self::$current_token;
        $tokenData = self::getTokenData($token);
        if (!$tokenData) {
            return false;
        }
        foreach ($data as $key => $item) {
            if (in_array($key, self::$allow_key) && empty($tokenData[$key])) {
                $tokenData[$key] = $item;
            }
        }
        return Helper::JsonSet('access_' . $token, $tokenData, $tokenData['expire_in'] - Carbon::now()->timestamp);
    }

    public static function getToken()
    {
        $token = $_SERVER['HTTP_TOKEN'] ?? null;
        return self::getTokenData($token) ? $token : null;
    }


    public static function authentication($request, $appInfo)
    {
        //权限校验
        list($app_id_form, $timestamp, $md5_string_param) = explode('@', base64_decode($request->input('encrypt')));
        if (Carbon::now()->timestamp - intval($timestamp) > 10) {
            return ['code' => 408, 'err' => '请求超时'];
        }
        if ($appInfo->app_id != $app_id_form) {
            return ['code' => 401, 'err' => '未授权'];
        }
        $md5_string = md5($timestamp . $appInfo->secret);
        if ($md5_string != $md5_string_param) {
            return ['code' => 401, 'err' => '未授权'];
        }

        return true;
    }

}
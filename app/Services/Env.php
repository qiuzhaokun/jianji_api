<?php
/**
 *
 * Created by: larry
 * DateTime: 25/12/2017 15:35
 */

namespace App\Services;

class Env extends BaseService
{
    public static function getUserId()
    {
        $token = Token::getTokenData();
        return $token['user_id'] ?? null;
    }

    public static function getAppId()
    {
        return $_SERVER['HTTP_APPID'] ?? null;
    }

    public static function getTimeStamp()
    {
        return $_SERVER['HTTP_TIMESTAMP'] ?? null;
    }

    public static function getUserInfo()
    {
        $token = Token::getTokenData();
        $user_info = [
            'user_id' => $token['user_id'] ?? null,
            'open_id' => $token['open_id'] ?? null,
            'mobile'  => $token['mobile'] ?? null,
        ];

        return array_filter($user_info);
    }

}
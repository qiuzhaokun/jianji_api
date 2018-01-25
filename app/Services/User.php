<?php
/**
 *
 * Created by: larry
 * DateTime: 20/12/2017 15:53
 */

namespace App\Services;

use Illuminate\Support\Facades\Config;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Log;

class User extends BaseService
{

    public static function findByWhere(array $where)
    {
        $info = UserModel::where($where)->first();
        return $info ? collect($info)->toArray() : [];
    }

    public static function findByOrWhere(array $orWhere)
    {
        $info = UserModel::orWhere($orWhere)->first();
        return $info ? collect($info)->toArray() : [];
    }


    public static function codeToOpenId(string $code)
    {
        $url = sprintf(Config::get('weChat.codeToOpenId.url'), $code);
        $weChatRes = json_decode(Helper::request($url), true);
        if (!empty($weChatRes['errcode']) && !empty($weChatRes['errmsg'])) {
            Log::error('weChat_code_to_openId', ['code' => $code, 'response' => $weChatRes]);
            return ['err' => $weChatRes['errmsg'], 'code' => $weChatRes['errcode']];
        }
        $open_id = $weChatRes['openid'] ?? null;
        $session_key = $weChatRes['session_key'] ?? null;
        $union_id = $weChatRes['unionid'] ?? null;

        if (!$open_id) {
            return ['err' => '登录失败，请重试！'];
        }
        //查询用户
        $user_info = self::findByWhere(['open_id' => $open_id]);
        if (empty($user_info->user_id)) {
            //注册用户
            $user_info = self::findByWhere(['union_id' => $union_id]);
            if (empty($user_info->user_id)) {
                //注册用户
                $user_info = UserModel::registerUser(['open_id' => $open_id], ['union_id' => $union_id]);
            }
        }
        //login
        $token_update = [
            'user_id'     => $user_info['user_id'] ?? null,
            'open_id'     => $user_info['open_id'] ?? null,
            'mobile'      => $user_info['mobile'] ?? null,
            'session_key' => $session_key,
            'union_id'    => $union_id,
        ];
        Token::updateTokenData($token_update);


        $tokenData = Token::getTokenData();
        return ['user_id' => $tokenData['user_id'], 'open_id' => $tokenData['open_id']];
    }


    /**
     * 先查找用户，没有用户注册用户，有用户则补全信息
     * @param $mobile
     * @param $openId
     * @return array
     */
    public static function loginByMobile($mobile, $openId)
    {
        //openId 查询
        $user_info = User::findByWhere(['open_id' => $openId]);
        if (!empty($user_info->user_id)) {
            //绑定手机号
            UserModel::updateUserInfo(['user_id' => $user_info->user_id], ['mobile' => $mobile], true);
        } else {
            $user_info = User::findByWhere(['mobile' => $mobile]);
            if (!empty($user_info->user_id)) {
                UserModel::updateUserInfo(['user_id' => $user_info->user_id], ['open_id' => $openId], true);
            } else {
                //注册用户
                $user_info = UserModel::registerUser(['mobile' => $mobile, 'open_id' => $openId]);
            }
        }
        //登录
        $token_update = [
            'user_id' => $user_info->user_id ?? null,
            'open_id' => $user_info->open_id ?? null,
            'mobile'  => $user_info->mobile ?? null
        ];
        Token::updateTokenData($token_update);
        $tokenData = Token::getTokenData();
        return [
            'user_id' => $tokenData['user_id'] ?: '',
            'mobile'  => $tokenData['mobile'] ?: '',
            'open_id' => $tokenData['open_id'] ?: ''
        ];
    }

    /**
     * 获取当前用户信息，登录
     * @return array
     */
    public static function getUserInfo()
    {
        $token_user_info = Env::getUserInfo();
        if (empty($token_user_info)) {
            return ['err' => '请先绑定用户。'];
        }

        $user_info = User::findByOrWhere($token_user_info);
        //登录
        $token_update = [
            'user_id' => $user_info->user_id ?? null,
            'open_id' => $user_info->open_id ?? null,
            'mobile'  => $user_info->mobile ?? null
        ];
        Token::updateTokenData($token_update);

        return collect($user_info)->toArray();
    }

}
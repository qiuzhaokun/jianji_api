<?php

namespace App\Http\Api;

use App\Services\Env;
use App\Services\Helper;
use App\Services\User;
use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * 用户登录 微信小程序code 获取openId 然后登陆
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }

        return User::codeToOpenId($request->input('code'));
    }

    /**
     * 以手机号注册用户或者绑定手机号
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginByMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'openId' => 'required',
            'code'   => 'required',
        ]);
        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        $verify = Helper::verifySmsCode($mobile, $code);
        if (!empty($verify['err'])) {
            return $verify;
        }

        return User::loginByMobile($mobile, $request->input('openId'));
    }

    /**
     * 获取当前用户信息, 登录
     */
    public function getUserInfo()
    {
        return User::getUserInfo();
    }

    public function updateUserInfo(Request $request)
    {
        $user_id = Env::getUserId();
        if (!$user_id) {
            return ['err' => '请登录'];
        }

        return UserModel::updateUserInfo(['user_id' => $user_id], $request->all());
    }
}

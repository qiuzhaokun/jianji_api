<?php

namespace App\Http\Api;

use App\Services\App;
use App\Services\Env;
use App\Services\Helper;
use App\Services\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function initToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auth' => 'required',
            'uin'  => 'required',
        ]);

        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }

        $app_id = Env::getAppId();
        $app_info = App::findByAppId($app_id);
        if (empty($app_info['app_id'])) {
            return ['status' => 401, 'err' => '未授权'];
        }

        $verify = Helper::authentication($request, $app_info);
        if (!empty($verify['err'])) {
            return $verify;
        }

        return Token::initToken($request, $app_info);
    }
}

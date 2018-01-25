<?php
/**
 *
 * Created by: larry
 * DateTime: 21/12/2017 11:29
 */

namespace App\Http\Api;

use App\Services\Common;
use App\Services\Helper;
use App\Services\RsaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class CommController extends Controller
{
    /**
     * 发送短信【验证码服务】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSmsCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }

        $mobile = $request->input('mobile');
        return Helper::sendSmsCode($mobile);
    }

    /**
     * 经纬度定位【腾讯geo】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat'  => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }

        $lat = $request->get('lat');
        $long = $request->get('long');

        $config = Config::get('common.location');
        do {
            //腾讯 geo
            $response = Helper::getTencentLocation($config['tencent'], $lat, $long);
            if (empty($response['err'])) {
                continue;
            }

            //百度 geo

            //高德 geo

        } while (0);

        return $response;
    }


    public function uploadImage(Request $request)
    {
        $name = $request->get('name');
        $file = $request->file($name);
        if (!$file) {
            return ['err' => '请选择图片'];
        }
        if ($file->getError() != 0) {
            return ['err' => $file->getErrorMessage(), 'status' => 422];
        }
        $res = Common::upload($file, $list = 'noteImage');
        return $res;
    }


    public function publicEncrypt(Request $request)
    {
        $encrypt = $request->get('str') ?: http_build_query($request->all());
        $encryptD = RsaService::publicEncrypt($encrypt);
        return ['encrypt' => $encryptD];
    }

    public function privateDecrypt(Request $request)
    {
        $encrypt = $request->get('str') ?: http_build_query($request->all());
        $encryptD = RsaService::privateDecrypt($encrypt);
        return ['decrypt' => $encryptD];
    }

}
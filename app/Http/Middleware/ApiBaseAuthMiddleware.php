<?php

namespace App\Http\Middleware;

use App\Services\Aes;
use App\Services\Helper;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ApiBaseAuthMiddleware
{

    private static $not_allow_field = [
        '_html'
    ];

    /**
     * api 基础检测
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        //特殊接口【文件上传】
        if($request->get('baseAuth')){
            return $next($request);
        }


        if (empty($request->header()['appid']) || empty($request->header()['timestamp']) || empty($request->header()['sign'])) {
            $err = ['status' => 401, 'msg' => '拒绝访问.', 'data' => ['info' => 'auth middleware args']];

            return Response()->json($err, 401);
        }

        $app_id = current($request->header()['appid']);
        $timestamp = current($request->header()['timestamp']);
        $sign = current($request->header()['sign']);
        $token = !empty($request->header()['token']) ? current($request->header()['token']) : '';

        if (App::environment('production') && Carbon::now()->timestamp - intval($timestamp) > 5) {
            $err = ['status' => 408, 'msg' => '请求超时', 'data' => []];

            return Response()->json($err, 401);
        }

        $app_info = \App\Services\App::findByAppId_cache($app_id);
        if (empty($app_id) || empty($app_info)) {
            $err = ['status' => 401, 'msg' => '无权限访问.', 'data' => []];
            return Response()->json($err, 401);
        }

        $args_all = $request->all();
        foreach (self::$not_allow_field as $item) {
            unset($args_all[$item]);
        }
        $param = array_merge($args_all, ['APPID' => $app_id, 'TIMESTAMP' => $timestamp, 'TOKEN' => $token]);
        ksort($param);
        $string = http_build_query($param);
        $result_sign = strtoupper(md5($string));


        if (App::environment('production') && $result_sign != $sign) {
            Log::error('middleware base sign error', ['route' => $request->url(), 'sign' => $result_sign, 'args_sign' => $sign, 'args' => $string]);
            $err = ['status' => 401, 'msg' => '无效请求', 'data' => []];
            return Response()->json($err, 401);
        }


        //post
        if (strtolower($request->method()) == 'post') {
            //post 请求解析加密参数
            if($request->input('encrypt')){
                Helper::postArgsDecrypt($request);
                unset($request['encrypt']);
            }
        }


        return $next($request);
    }
}

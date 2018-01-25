<?php
/**
 * Des:
 * Author: larry
 * Date: 21/01/2018
 * Time: 2:15 PM
 */

namespace App\Http\Middleware;

use App\Services\Aes;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class PostEncryptMiddleware
{
    /**
     * API post 解密参数 中间件
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //post
        if (strtolower($request->method()) == 'post' && App::environment('production')) {
            if (empty($request->input('encrypt'))) {
                return ['status' => 401, 'msg' => '拒绝访问.', 'data' => ['info' => 'auth middleware encrypt']];
            }
            //post 请求解析加密参数
            Aes::getPostArgs($request);
            unset($request['encrypt']);
        }
        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use App\Services\Token;
use Carbon\Carbon;
use Closure;

class ApiTokenAuthMiddleware
{
    /**
     * api 基础检测
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->header()['token'])) {
            return ['status' => 401, 'err' => '拒绝访问.', 'data' => ['info' => 'token middleware'],];
        }

        $token = current($request->header()['token']);

        $tokenData = Token::getTokenData($token);
        if (empty($tokenData)) {
            return ['status' => 406, 'err' => 'token 已失效.', 'data' => []];
        }
        if ($tokenData['expire_in'] - Carbon::now()->timestamp < 0) {
            return ['status' => 406, 'err' => 'token 已过期.', 'data' => []];
        }

        //根据token限制频率
        /*$limit_key = 'limit_token_count_' . $token;
        $count = Helper::getCache($limit_key);
        if ($count && $count > Config::get('common.api_limit.token_limit')) {
            $time = Redis::ttl($limit_key);
            return ['status' => 429, 'err' => '访问过于频繁，请休息一下.', 'data' => ['expire' => $time, 'count' => $count]];
        }
        Helper::setCache($limit_key, $count + 1);
        if (!$count) {
            Redis::expire($limit_key, 60);
        }*/

        //设置当前 token
        Token::$current_token = $token;

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class ApiResponseJson
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
        $data = $next($request)->original;
        if (!is_array($data)) {
            return $next($request);
        }
        $status = 200;
        $msg = 'ok';
        if (!empty($data['msg'])) {
            $data = $data['data'] ?? [];
        }
        if (!empty($data['err'])) {
            $status = $data['status'] ?? 500;
            $msg = $data['err'] ?? 'fail';
            $data = $data['data'] ?? [];
        }

        if (!empty($data['status'])) {
            $status = $data['status'];
            $msg = $data['msg'] ?? $msg;
            $data = $data['data'] ?? [];
        }
        $response = ['status' => $status, 'msg' => $msg, 'data' => $data];
        return Response()->json($response, $status);
    }
}

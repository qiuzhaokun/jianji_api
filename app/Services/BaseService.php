<?php
/**
 *
 * Created by: larry
 * DateTime: 19/12/2017 15:23
 */

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Mockery\CountValidator\Exception;

class BaseService
{

    public static function __callStatic($method, $parameters)
    {
        //统一缓存
        try {
            //统一缓存机制 调用方法：method_cache
            if (strpos($method, '_cache')) {
                $class = get_called_class();
                $method = rtrim($method, '_cache');
                $cache_key = str_replace('\\', '_', 'cache_' . $class . $method . '_' . md5(json_encode($parameters)));
                $cache = Helper::getCache($cache_key);
                if ($cache) {
                    return $cache;
                }
                //调用
                if (!class_exists($class)) {
                    return ['status' => 500, 'err' => "class $class not found."];
                }
                if (!method_exists($class, $method)) {
                    return ['status' => 500, 'err' => "method $class not found."];
                }
                //调用真实的方法
                $response = call_user_func_array([$class, $method], $parameters);
                if ($response) {
                    Helper::setCache($cache_key, $response, 60);
                }
                return $response;
            }

        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => $method, 'args' => $parameters]);
            return [];
        }
    }
}
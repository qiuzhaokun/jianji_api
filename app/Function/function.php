<?php
/**
 * Des: 自定义函数库
 * Author: larry
 * Date: 23/01/2018
 * Time: 2:54 PM
 */

if (!function_exists('url_safe_base64_encode')) {
    function url_safe_base64_encode($data)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($data));
    }
}

if (!function_exists('url_safe_base64_decode')) {
    function url_safe_base64_decode($data)
    {
        return base64_decode($data);
        $base_64 = str_replace(array('-', '_'), array('+', '/'), $data);
    }
}
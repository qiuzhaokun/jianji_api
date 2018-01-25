<?php
/**
 *
 * Created by: larry
 * DateTime: 19/12/2017 15:23
 */

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Overtrue\EasySms\EasySms;

class Helper extends BaseService
{

    public static function jsonGet(string $key)
    {
        Redis::select(Config::get('cache.storage_default_db'));
        $res = Redis::get($key);
        return json_decode($res, true);
    }

    public static function jsonSet(string $key, $data, int $expire_time = 0)
    {
        Redis::select(Config::get('cache.storage_default_db'));
        if ($expire_time) {
            return Redis::setex($key, intval($expire_time), json_encode($data));
        } else {
            return Redis::set($key, json_encode($data));
        }
    }

    public static function setCache(string $key, $data, int $expire_time = 0)
    {
        Redis::select(Config::get('cache.cache_default_db'));
        if ($expire_time) {
            return Redis::setex($key, intval($expire_time), json_encode($data));
        } else {
            return Redis::set($key, json_encode($data));
        }
    }

    public static function getCache(string $key)
    {
        Redis::select(Config::get('cache.cache_default_db'));
        $res = Redis::get($key);
        return json_decode($res, true);
    }


    public static function uuid(): string
    {
        $id = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($id, 0, 8)
            . substr($id, 8, 4)
            . substr($id, 12, 4)
            . substr($id, 16, 4)
            . substr($id, 20, 12);
        return $uuid;
    }

    public static function getIp(): string
    {
        if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = "0.0.0.0";
        }
        return $ip;
    }


    public static function request(string $url, string $method = 'GET', array $param = [], array $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        if ($method == "POST") {
            curl_setopt($url, CURLOPT_POST, 1);
            $data = http_build_query($param);
            curl_setopt($url, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        if ($curlErrNo) {
            $curlError = curl_error($ch);
            Log::error('curl request fail.', [
                'url'      => $url,
                'method'   => $method,
                'args'     => $param,
                'response' => $output,
                'error'    => ['status' => $curlErrNo, 'info' => $curlError]
            ]);
            return false;
        }

        curl_close($ch);
        return $output;
    }


    public static function verifySmsCode($mobile, $code)
    {
        return true;
        $cache_code = Redis::get('sms_code_' . $mobile);
        return (bool)($cache_code == $code);
    }

    public static function sendSmsCode(string $mobile, string $template = null)
    {
        if (!preg_match("/^1[34578]\d{9}$/", $mobile)) {
            return ['err' => '请输入正确的手机号.'];
        }

        //发送次数限量
        $res_limit = self::sendSmsLimit($mobile);
        if (!empty($res_limit['err'])) {
            return $res_limit;
        }
        $code = mt_rand(100000, 999999);

        Redis::setex('sms_code_' . $mobile, 120, $code);

        $config = Config::get('aliyun.sms');

        $easySms = new EasySms($config);

        $response = $easySms->send($mobile, [
            'content'  => '您的验证码为: 6379',
            'template' => $template ?: Config::get('aliyun.sms.default_template'),
            'data'     => ['username' => '用户', 'code' => $code]
        ]);

        if ($response['aliyun']['result']['Message'] == 'OK') {
            return ['msg' => '发送成功.', 'data' => (array)$response];
        } else {
            Log::error('sendSmsCode_error', ['mobile' => $mobile, 'response' => (array)$response]);
            return ['err' => '短信发送过于频繁，请稍后重试！', 'data' => ['info' => $response['aliyun']['result']['Message']]];
        }
    }

    public static function sendSmsLimit(string $mobile)
    {
        //token 限流
        $token = Token::getToken();
        $num = Helper::getCache('sms_code_num_token_' . $token);
        if ($num > Config::get('aliyun.sms.limit.token')) {
            return ['err' => '短信发送达到限量，请稍后重试！'];
        }
        Helper::setCache('sms_code_num_token_' . $token, 3600, $num + 1);

        //手机号限流
        $num = Helper::getCache('sms_code_num_mobile_' . $mobile);
        if ($num > Config::get('aliyun.sms.limit.mobile')) {
            return ['err' => '短信发送达到限量，请稍后重试！'];
        }
        Helper::setCache('sms_code_num_mobile_' . $mobile, 3600, $num + 1);

        return true;
    }

    public static function authentication($request, $appInfo)
    {
        //权限校验
        list($app_id_form, $timestamp, $md5_string_param) = explode('@', base64_decode($request->auth));
        if (App::environment('production') && Carbon::now()->timestamp - intval($timestamp) > 10) {
            return ['code' => 408, 'err' => '请求超时'];
        }
        if ($appInfo['app_id'] != $app_id_form) {
            return ['code' => 401, 'err' => '未授权'];
        }
        $md5_string = md5($timestamp . $appInfo['secret']);
        if ($md5_string != $md5_string_param) {
            return ['code' => 401, 'err' => '未授权'];
        }

        return true;
    }

    public static function getTencentLocation($config, $lat, $long)
    {
        $api_url = $config['api'] . http_build_query([
                'key'      => $config['key'],
                'location' => $lat . ',' . $long,
            ]);
        $client = new Client();
        $response = $client->request('GET', $api_url);
        $body = json_decode($response->getBody(), true);

        if (!isset($body['status']) || $body['status'] != 0) {
            return ['err' => '定位失败', 'data' => $body];
        }

        $res = $body['result']['address_component'];
        $res['address'] = $body['result']['address'];

        return $res;
    }


    public static function numToZh($num)
    {
        if (empty($num)) {
            return false;
        }
        $num_org = str_split('0123456789', 1);
        $num_arr = str_split($num, 1);
        $cns = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
        $result_arr = [];
        foreach ($num_arr as $key => $value) {
            $result_arr[] = $cns[$num_org[$value]];
        }
        return implode('', $result_arr);
    }

    public static function formatWeek(&$week)
    {
        $num = self::numToZh($week);
        if ($num == 7) {
            $num = '天';
        }
        $week = '星期' . $num;
        return true;
    }

    public static function formatDate(&$value)
    {
        if (strlen($value) > 1) {
            $tmp = mb_substr($value, 0, 1) != '一' ? mb_substr($value, 0, 1) : '';
            $value = $tmp . '十' . mb_substr($value, 1);
        }
        return true;
    }


    public static function timeTran($time)
    {
        $text = '';
        if (!$time) {
            return $text;
        }
        $time = strtotime($time);
        $current = time();
        $t = $current - $time;
        $retArr = array('刚刚', '秒前', '分钟前', '小时前', '天前', '月前', '年前');
        switch ($t) {
            case $t < 0://时间大于当前时间，返回格式化时间
                $text = date('Y-m-d', $time);
                break;
            case $t == 0://刚刚
                $text = $retArr[0];
                break;
            case $t < 60:// 几秒前
                $text = $t . $retArr[1];
                break;
            case $t < 3600://几分钟前
                $text = floor($t / 60) . $retArr[2];
                break;
            case $t < 86400://几小时前
                $text = floor($t / 3600) . $retArr[3];
                break;
            case $t < 2592000: //几天前
                $text = floor($t / 86400) . $retArr[4];
                break;
            case $t < 31536000: //几个月前
                $text = floor($t / 2592000) . $retArr[5];
                break;
            default : //几年前
                $text = floor($t / 31536000) . $retArr[6];
        }
        return $text;
    }


    public static function buildPath(array $data)
    {
        if (!$data) {
            return '';
        }
        return $data['scheme'] .'://' . $data['host'] . $data['path'] . '?' . $data['query'];
    }


    public static function postArgsDecrypt($request)
    {
        $encrypt = $request->input('encrypt');
        $decrypt = RsaService::privateDecrypt($encrypt);
        if (!$decrypt) {
            echo json_encode(['status' => 402, 'msg' => '请求失败，请重试！', 'data' => ['info' => 'rsa decrypt err']]);
            exit;
        }
        parse_str($decrypt, $result);
        //解密结果合并到 $request
        $request->merge($result);
        return true;
    }

}
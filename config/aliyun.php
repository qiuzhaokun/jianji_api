<?php
/**
 *
 * Created by: larry
 * DateTime: 21/12/2017 11:23
 */

$access_key = 'LTAIkzfHxPtBCh4u';
$access_secret = 'bLhls08koothQ5RKghnxE8nbWm9Q5y';

return [

    'access_key'    => $access_key, // accessKey
    'access_secret' => $access_secret, // accessSecret

    'oss' => [
        'endpoint'  => 'http://oss-cn-beijing.aliyuncs.com',
        'bucket'    => 'jianji-static',
        //'read_host' => 'https://jianji-static.oss-cn-beijing.aliyuncs.com',
        'read_host' => 'static-jianji.myexist.cn',
        'read_image_timeout' => 3600 * 24,
    ],


    'sms' => [
        'timeout'          => 5.0,
        'default'          => [
            'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
            'gateways' => [
                'aliyun',
            ],
        ],
        'gateways'         => [
            'errorlog' => [
                'file' => storage_path('easy-sms.log'),
            ],
            'aliyun'   => [
                'access_key_id'     => $access_key,
                'access_key_secret' => $access_secret,
                'sign_name'         => '简记',
            ]
        ],
        'default_template' => 'SMS_25360282',
        'limit'            => [
            'token'  => 5,
            'mobile' => 5,
        ],
    ],


];
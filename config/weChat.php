<?php
/**
 *
 * Created by: larry
 * DateTime: 20/12/2017 15:56
 */

$appId = 'wx37aa678dd47fca6c';
$secret = '5da5e47a9d6e4b14ff6db55ffd02c4af';

return [

    'app' => [
        'appId' => $appId,
        'secret' => $secret,
    ],

    'codeToOpenId' => [
        'url' => 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appId.'&secret='.$secret.'&js_code=%s&grant_type=authorization_code'
    ],
];
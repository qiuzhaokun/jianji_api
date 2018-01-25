<?php
/**
 * Des:
 * User: larry
 * Date: 08/01/2018
 * Time: 5:31 PM
 */

return [
    'api_limit' => [
        'token_limit' => 50,
    ],


    'location' => [
        //腾讯geo 定位服务
        'tencent' => [
            'api' => 'http://apis.map.qq.com/ws/geocoder/v1/?',
            'key' => 'OZSBZ-K5CCG-4X2QJ-ID2E2-SMJK2-QHFK5',
        ],
    ],

    'upload' => [
        'max_size'       => 5, //单位M
        'allow_mimeType' => ['jpeg', 'jpg', 'png', 'gif',],
    ],
];
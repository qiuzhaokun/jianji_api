<?php
/**
 * Des:
 * Author: larry
 * Date: 21/01/2018
 * Time: 12:35 PM
 */

$data = [
    'status' => 500,
    'msg' => '系统错误，请联系管理员,微信号：BinGer1_992',
    'data' => ['nginx redirect'],
];

echo json_encode($data);
exit;
<?php
/**
 * Des:
 * Author: larry
 * Date: 15/01/2018
 * Time: 3:20 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use JohnLui\AliyunOSS;
use Mockery\Exception;

class Common extends BaseService
{
    public static function upload($file, string $list = null)
    {
        //检测文件
        $config = Config::get('common.upload');
        if ((round($file->getSize() / 1048576, 2)) > $config['max_size']) {
            return ['err' => '上传文件过超过限制，请控制在5M以内'];
        }
        $mineType = explode('/', $file->getMimeType())[1];
        if (!in_array($mineType, $config['allow_mimeType'])) {
            return ['err'  => '文件类型不允许上传，请选择如下类型' . implode(',', $config['allow_mimeType']),
                    'data' => ['allow_mimeType' => $config['allow_mimeType']]];
        }
        $fileName = $list . '/' ?: '';
        $fileName .= date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $mineType;

        $filePath = $file->getPathName();

        //上传到aliyun oss
        $ossClient = new \App\Services\Util\AliYunOss();
        $ossClient->setBucket();
        $ossClient->uploadFile($fileName, $filePath);

        $signPath = $ossClient->getFileSignUrl($fileName);
        return [
            'url'      => $signPath,
            'host'     => config('aliyun.oss.read_host'),
            'path'     => $fileName,
            'size'     => round($file->getSize() / 1024, 2), //单位K
            'mimeType' => $file->getMimeType(),
        ];
    }
}
<?php
/**
 * Des:
 * Author: larry
 * Date: 15/01/2018
 * Time: 5:45 PM
 */

namespace App\Services\Util;


use App\Services\Helper;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use OSS\OssClient;

class AliYunOss
{
    public $ossClient;
    private $butcket;

    public function __construct()
    {
        $access_keyId = config('aliyun.access_key');
        $access_secret = config('aliyun.access_secret');
        $endpoint = config('aliyun.oss.endpoint');
        $this->ossClient = new OssClient($access_keyId, $access_secret, $endpoint);

        $this->butcket = config('aliyun.oss.bucket');
    }


    public function setBucket(string $bucket = null)
    {
        $this->butcket = $bucket;
    }

    public function uploadFile(string $fileName, string $filePath, array $options = [])
    {
        try{
            $value = $this->ossClient->uploadFile($this->butcket, $fileName, $filePath, $options);
            return $value;
        }catch (Exception $e){
            Log::error($e->getMessage());
            return ['err' => $e->getMessage()];
        }

    }

    public function getFileSignUrl(string $fileName)
    {
        $url = $this->ossClient->signUrl($this->butcket, $fileName, config('aliyun.oss.read_image_timeout'));
        $query = parse_url($url);
        $query['host'] = config('aliyun.oss.read_host');
        return Helper::buildPath($query);
    }

}
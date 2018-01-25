<?php
/**
 * app access
 * Created by: larry
 * DateTime: 19/12/2017 15:31
 */

namespace App\Models;

class AppAccess extends BaseModels
{

    public $primaryKey = 'app_id';
    public $table = 'app_access';
    public $incrementing = false;

    public static function getAppInfoById(string $appId, string $field = null)
    {
        if (!$appId) {
            return null;
        }
        $data = self::where('app_id', $appId)->first();
        $data = collect($data)->toArray();
        if ($data && $field) {
            $data = $data[$field] ?? null;
        }
        return $data;
    }

}
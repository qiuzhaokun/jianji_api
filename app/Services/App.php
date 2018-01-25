<?php
/**
 *
 * Created by: larry
 * DateTime: 25/12/2017 18:16
 */

namespace App\Services;

use App\Models\AppAccess;
use Carbon\Carbon;

class App extends BaseService
{
    public static function findByAppId(string $appId)
    {
        $timestamp = Carbon::now()->toDateTimeString();
        $db_res = AppAccess::find($appId);
        if (empty($db_res)) {
            return [];
        }
        $db_res = $db_res->first();
        if (($timestamp >= $db_res->start_time && $timestamp <= $db_res->end_time)) {
            return collect($db_res)->toArray();
        }
        return [];
    }

}
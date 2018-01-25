<?php

namespace App\Models;

use App\Services\Helper;
use Carbon\Carbon;

class User extends BaseModels
{
    public $primaryKey = 'user_id';
    public $table = 'users';
    public $incrementing = false;

    private static $not_allow_update_field = [
        'user_id'
    ];

    public static function registerUser(array $register_info, array $info = [])
    {
        $user_info = \App\Services\User::findByOrWhere($register_info);
        if (empty($user_info['user_id'])) {
            $data = array_filter(array_merge($register_info, $info));
            $time = Carbon::now()->toDateTimeString();
            $data['user_id'] = Helper::uuid();
            $data['created_at'] = $data['updated_at'] = $time;
            $res = self::insert($data);
            if ($res) {
                $user_info = \App\Services\User::findByWhere(['user_id' => $data['user_id']]);
            }
        }
        return $user_info;
    }


    /**
     * 更新用户信息
     * @param array $where
     * @param array $update
     * @param bool $verifyExist 是否检查库数据存在，存在则不更新
     * @return mixed
     */
    public static function updateUserInfo(array $where, array $update, $verifyExist = false)
    {
        $updateData = $update;

        if ($verifyExist) {
            $user_info = \App\Services\User::findByWhere($where);
            $updateData = [];
            foreach ($update as $key => $item) {
                if (!empty($user_info->$key)) {
                    $updateData[$key] = $item;
                }
            }
        }

        //过滤是否允许更新字段
        foreach ($updateData as $key => $value) {
            if (in_array($key, self::$not_allow_update_field)) {
                unset($updateData[$key]);
            }
        }

        return self::where($where)->update($updateData);
    }


}

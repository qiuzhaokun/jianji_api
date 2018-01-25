<?php
/**
 *
 * Created by: larry
 * DateTime: 21/12/2017 15:10
 */

namespace App\Services;

use App\Models\NoteModel;
use App\Models\SupportModel;
use App\Models\User as UserModel;
use Carbon\Carbon;

class Note extends BaseService
{
    private static $allow_show_field = [
        'note_id',
        'title',
        'content',
        'created_at',
        'updated_at',
        'user_id',
        'nick_name',
        'avatar',
        'time_tran',
        'image'
    ];


    public static function findByWhere(array $where)
    {
        $info = NoteModel::select('*', 'updated_at AS time_tran')->where($where)->first();
        return $info ? collect($info)->toArray() : [];
    }


    public static function getNoteList($user_id, $size, $share = null)
    {
        $list = NoteModel::getNoteListPaginate($user_id, $size, $share);
        unset($list['path'], $list['to'], $list['from']);
        return $list;
    }

    public static function getNoteDetail($note_id)
    {
        $detail = Note::findByWhere(['note_id' => $note_id]);
        $detail = collect($detail)->toArray();
        if (!empty($detail['err'])) {
            return $detail;
        }
        //用户信息
        $user_info = User::findByWhere(['user_id' => $detail['user_id']]);

        $detail = array_merge($detail, $user_info);

        foreach ($detail as $key => $item) {
            if (!in_array($key, self::$allow_show_field)) {
                unset($detail[$key]);
            }
        }
        return $detail;
    }

    public static function noteSupport(string $note_id)
    {
        $note_detail = Note::findByWhere(['note_id' => $note_id]);
        if (empty($note_detail)) {
            return ['err' => '失败，请重试！'];
        }
        $data = [];
        $data['support_id'] = Helper::uuid();
        $data['user_id'] = Env::getUserId();
        $data['note_id'] = $note_id;
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $response = SupportModel::insert($data);
        if (!$response) {
            return ['err' => '支持失败，请重试！'];
        }
        return ['support_id' => $data['support_id']];
    }

}
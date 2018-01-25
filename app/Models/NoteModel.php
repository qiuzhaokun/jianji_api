<?php

namespace App\Models;

use App\Services\Env;
use App\Services\Helper;
use App\Services\Util\AliYunOss;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class NoteModel extends BaseModels
{
    use SoftDeletes;

    public $primaryKey = 'note_id';
    public $table = 'notes';
    public $incrementing = false;

    public $fillable = ['note_id', 'content', 'image', 'user_id' , 'support_sum', 'share', 'date_zh', 'created_at', 'updated_at', 'deleted_at'];

    public function setDateZhAttribute($value)
    {
        $value = strtotime($value);
        $year = Helper::numToZh(date('Y', $value));
        $month = Helper::numToZh(date('m', $value));
        $date = Helper::numToZh(date('j', $value));
        Helper::formatDate($month);
        Helper::formatDate($date);

        $hour = date('G', $value);
        $min = date('i', $value);
        $week = date('N', $value);
        Helper::formatWeek($week);
        return $year . '年' . $month . '月' . $date . '日 ' . $hour . ':' . $min. ' '. $week;
    }



    public function getImageAttribute($value)
    {
        if($value){
            $client = new AliYunOss();
            return $client->getFileSignUrl($value);
        }
        if(is_null($value)){
            return '';
        }
    }


    public function getTimeTranAttribute($value)
    {
        return Helper::timeTran($value);
    }

    public static function findWhere(array $where, string $filed = null)
    {
        $res = self::where($where)->first();
        if(!empty($res->note_id)){
            $arr = collect($res)->toArray();
            if($filed){
                $filed =  array_filter(explode(',', $filed));
                $res_arr = [];
                foreach ($filed as $value){
                    if(!empty($arr[$value])){
                        $res_arr[$value] = $arr[$value];
                    }
                }
                return $res_arr;
            }
            return $arr;
        }
        return [];
    }

    public static function getNoteListPaginate(string $user_id = null, int $size = 10, $share = null)
    {
        $response = [];
        try {
            $sql = self::select('notes.note_id', 'notes.image', 'notes.content', 'notes.updated_at', 'notes.support_sum'
                , 'users.user_id', 'users.nick_name', 'users.avatar', 'notes.updated_at AS update_date_zh', 'notes.updated_at AS time_tran')
                ->join('users', 'notes.user_id', '=', 'users.user_id');
            if ($user_id) {
                $sql = $sql->where('notes.user_id', $user_id);
            }
            if (!is_null($share)) {
                $sql = $sql->where('notes.share', intval($share));
            }
            $response = $sql->whereNull('notes.deleted_at')
                ->orderBy('updated_at', 'desc')
                ->paginate($size)->toArray();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return ['err' => $exception->getMessage()];
        }
        return $response;
    }

    public static function createNote($data)
    {
        $saveData = [];
        $saveData['note_id'] = Helper::uuid();
        $saveData['user_id'] = Env::getUserId();
        $saveData['content'] = $data['content'] ?? '';
        $saveData['image'] = $data['image'] ?? '';
        $saveData['created_at'] = $data['updated_at'] = Carbon::now()->toDateTimeString();
        return self::create($saveData);
    }

    public static function updateNote(string $note_id, array $update)
    {
        $allow_filed = ['title', 'content', 'password', 'image'];
        $data = [];
        foreach ($update as $key => $item) {
            if (in_array($key, $allow_filed) && !empty($item)) {
                $data[$key] = $item;
            }
        }
        $data['password'] = empty($update['password']) ? '' : password_hash($update['password'], PASSWORD_BCRYPT);
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $where = ['note_id' => $note_id, 'user_id' => Env::getUserId()];
        $note = self::where($where);
        foreach ($data as $field => $item){
            $note->$field = $item;
        }
        return $note->save();
    }

    public static function deleteMyNote(string $note_id)
    {
        $where = ['user_id' => Env::getUserId(), 'note_id' => $note_id];
        return self::where($where)->delete();
    }

    public static function updateNoteShareStatus(string $noteId)
    {
        $where = ['user_id' => Env::getUserId(), 'note_id' => $noteId];
        return self::where($where)->update(['share' => DB::raw("IF(share = 0, 1, 0)"), 'updated_at' => Carbon::now()->toDateTimeString()]);

        //@TODO 若有图片更新，则把旧图片删除
    }

}

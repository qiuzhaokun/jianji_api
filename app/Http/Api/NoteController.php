<?php
/**
 *
 * Created by: larry
 * DateTime: 21/12/2017 14:49
 */

namespace App\Http\Api;

use App\Models\NoteModel;
use App\Services\Env;
use App\Services\Helper;
use App\Services\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{

    public function getMyNoteList(Request $request)
    {
        $size = $request->input('size', 10);

        $user_id = Env::getUserId();
        $note_list = Note::getNoteList($user_id, $size);

        return $note_list;
    }

    public function getNoteList(Request $request)
    {
        $size = $request->input('size') ?: 10;

        return Note::getNoteList(null, $size, $share = 1);
    }

    public function getNoteDetail(Request $request, string $note_id)
    {
        return Note::getNoteDetail($note_id);
    }


    public function saveNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        $note_id = $request->get('note_id');

        if ($validator->fails()) {
            return ['status' => 402, 'err' => $validator->errors()];
        }

        $user_id = Env::getUserId();
        if (!$user_id) {
            return ['status' => 405, 'err' => '请先登录'];
        }

        if ($note_id) {
            //update
            $response = NoteModel::updateNote($note_id, $request->all());
            $response = ['updated' => (bool)$response->note_id];
        } else {
            //insert
            $response = NoteModel::createNote($request->all());
            $response = ['created' => (bool)$response->note_id];
        }
        return ['status' => 201, 'data' => $response];

    }


    public function deleteNote($note_id)
    {
        $user_id = Env::getUserId();
        if (!$user_id) {
            return ['err' => '请先登录', 'status' => 405];
        }

        $result_del = NoteModel::deleteMyNote($note_id);
        $result_del = ['deleted' => $result_del];
        return $result_del;
    }

    public function noteSupport($note_id)
    {
        $user_id = Env::getUserId();
        if (!$user_id) {
            return ['status' => 405, 'err' => '请先登录'];
        }
        return Note::noteSupport($note_id);
    }

    public function updateNoteStatus($note_id)
    {
        $res = NoteModel::updateNoteShareStatus($note_id);
        if (!$res) {
            return ['err' => '更新失败', 'data' => ['response' => $res]];
        }
        return ['updated' => (bool)$res];
    }
}
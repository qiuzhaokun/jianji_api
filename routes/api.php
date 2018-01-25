<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$api->post('/token', 'AuthController@initToken');

$api->post('/publicEncrypt', 'CommController@publicEncrypt');
$api->post('/decrypt', 'CommController@privateDecrypt');

$api->group(['middleware' => 'api_token_auth'], function ($api) {


    $api->post('login', 'UserController@login');
    $api->post('loginByMobile', 'UserController@loginByMobile');


    $api->post('sendSmsCode', 'CommController@sendSmsCode');

    $api->post('support/{note_id}', 'NoteController@noteSupport');


    //file upload
    $api->post('upload', 'CommController@uploadImage');

    $api->get('userInfo', 'UserController@getUserInfo');


    $api->get('location', 'CommController@location');
    //$api->post('upload', 'CommController@uploadImage');

    $api->get('note', 'NoteController@getNoteList');
    $api->post('note', 'NoteController@saveNote');
    $api->get('myNote', 'NoteController@getMyNoteList'); //我的日记列表
    $api->get('note/{note_id}', 'NoteController@getNoteDetail');
    $api->delete('note/{note_id}', 'NoteController@deleteNote');
    $api->post('noteStatus/{note_id}', 'NoteController@updateNoteStatus');

});





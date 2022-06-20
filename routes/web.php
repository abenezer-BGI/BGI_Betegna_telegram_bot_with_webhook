<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/lutipxfegswfgpoygrzqkiphezeqmfbwhdrswxbazoegtfdskozlbmerpydkexcy/webhook', function () {
    $update = Telegram::commandsHandler(true);

//    if ((!is_null($update->message->text) and !str_starts_with($update->message->text, '/')) or !is_null($update->callback_query)) {
//        $bot = new \Telegram\Bot\Api();
//        (new \App\Telegram\UpdateHandlers\RootUpdateHandler($bot,$update))->handle();
//    }

    return 'ok';
});

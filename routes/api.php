<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['cors'])->group(function () {

    Route::post('generate_first_page', ['App\Http\Controllers\Api\ApiRevoChatgptController', 'generate_first_page']);
    Route::post('generate_exam', ['App\Http\Controllers\Api\ApiRevoChatgptController', 'generate_exam']);
    Route::post('generate_tutorial', ['App\Http\Controllers\Api\ApiRevoChatgptController', 'generate_tutorial']);
    Route::post('generate_presentation', ['App\Http\Controllers\Api\ApiRevoChatgptController', 'generate_presentation']);

    Route::get('fetch', ['App\Http\Controllers\Api\ChatgptApiController', 'fetch']);
    Route::post('send', ['App\Http\Controllers\Api\ChatgptApiController', 'send']);

    Route::post('chatgpt', ['App\Http\Controllers\ChatgptController', 'sheets_test']);
});
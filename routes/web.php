<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WordpressController;

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

Route::get('tester', ['App\Http\Controllers\Api\ApiRevoChatgptController', 'test']);


Route::get('runartisan', function () {

    Artisan::call('optimize:clear');

    dd(Artisan::output());
});

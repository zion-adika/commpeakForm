<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/calls', 'App\Http\Controllers\CallsController@index');

//Route::get('calls', 'FileController@index');
Route::post('store-file', 'App\Http\Controllers\CallsController@store');

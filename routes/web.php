<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});

////////////////////////////////////
/// 获取商家列表API
Route::get('/businesslist','\App\Http\Controllers\ApiController@businesslist');
Route::get('/business','\App\Http\Controllers\ApiController@business');
Route::post('/regist/','\App\Http\Controllers\ApiController@regist');
Route::post('/logincheck','\App\Http\Controllers\ApiController@logincheck');
Route::get('/sms','\App\Http\Controllers\ApiController@sms');
Route::get('/changepassword','\App\Http\Controllers\ApiController@changepassword');
Route::get('/addresslist','\App\Http\Controllers\ApiController@addresslist');
Route::post('/addaddress','\App\Http\Controllers\ApiController@addaddress');
Route::post('/editaddress','\App\Http\Controllers\ApiController@editaddress');
Route::get('/address','\App\Http\Controllers\ApiController@address');
Route::post('/addcart','\App\Http\Controllers\ApiController@addcart');
Route::get('/cart','\App\Http\Controllers\ApiController@cart');
Route::post('/addorder','\App\Http\Controllers\ApiController@addorder');
Route::get('/order','\App\Http\Controllers\ApiController@order');
Route::get('/orderlist','\App\Http\Controllers\ApiController@orderlist');

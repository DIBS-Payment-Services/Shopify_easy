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
Route::get('auth', 'AuthApp');
Route::get('install', 'InstallApp');
Route::post('pay', 'Pay');
Route::post('postForm', 'MerchantSettings@store');
Route::get('form', 'MerchantSettings@index');
Route::get('return', 'Accept');
Route::get('/', 'Index@index');


Route::get('test', 'Test');


Route::post('capture', 'Capture');

Route::post('charge_created', 'ChargeCreatedEasyHook');
Route::post('order_created', 'OrderCreatedHook');
Route::post('charge_created', 'ChargeCreatedEasyHook');

Route::get('installinit', 'Index@install');
Route::post('callback', 'Callback');
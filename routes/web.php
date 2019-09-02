<?php

use App\Http\Middleware\CheckHmack;

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
Route::get('auth', 'AuthApp')->middleware(CheckHmack::class);
Route::get('install', 'InstallApp');

Route::post('pay', 'Pay');
Route::post('pay_t', 'PayTest');

Route::post('capture', 'Capture');
Route::post('capture_t', 'CaptureTest');

Route::post('void', 'Cancel');
Route::post('void_t', 'CancelTest');

Route::post('refund', 'Refund');
Route::post('refund_t', 'RefundTest');

Route::post('postForm', 'MerchantSettings@store');
Route::get('form', 'MerchantSettings@index');
Route::get('return', 'Accept');

Route::get('return_t', 'AcceptTest');
Route::get('/', 'Index@index');
Route::get('test', 'Test');

Route::post('charge_created', 'ChargeCreatedEasyHook');
Route::post('order_created', 'OrderCreatedHook');

Route::post('charge_created', 'ChargeCreatedEasyHook');

Route::post('refund_hook', 'RefundEasyHook');
Route::post('cancel_hook', 'CancelEasyHook');

Route::get('installinit', 'Index@install');
Route::post('callback', 'Callback');
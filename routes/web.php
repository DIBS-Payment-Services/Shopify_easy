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

Route::post('pay', 'Pay\Pay');
Route::post('pay_t', 'Pay\PayTest');

Route::post('capture', 'Capture\Capture');
Route::post('capture_t', 'Capture\CaptureTest');

Route::post('void', 'Void\Cancel');
Route::post('void_t', 'Void\CancelTest');

Route::post('refund', 'Refund\Refund');
Route::post('refund_t', 'Refund\RefundTest');

Route::post('postForm', 'MerchantSettings@store');
Route::get('form', 'MerchantSettings@index');

Route::get('return', 'Accept\Accept');
Route::get('return_t', 'Accept\AcceptTest');

Route::get('/', 'Index@index');
Route::get('test', 'Test');

Route::post('order_created', 'OrderCreatedHook');

Route::post('charge_created', 'EasyWebHooks\ChargeCreatedEasyHook');

Route::post('refund_hook', 'EasyWebHooks\RefundEasyHook');
Route::post('cancel_hook', 'EasyWebHooks\CancelEasyHook');

Route::get('installinit', 'Index@install');

Route::post('callback', 'EasyWebHooks\Callback');

Route::get('key', 'Util');

Route::post('key', 'Util');

Route::any('accept_d2', 'D2\Accept');
Route::post('callback_d2', 'D2\Callback');

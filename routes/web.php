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

});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware('basic_auth');
Route::get('test_payment', function () {
    return view('test_payment');
});
Route::get('payment/pgw', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'index'])->name('payment.pgw');
Route::get('payment/pgw/{paymentMerchantCode}/result', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'result'])->name('payment.pgw.result');
Route::get('payment/notify', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'notify'])->name('payment.notify');
Route::get('verified_contact/notify', [\App\Http\Controllers\Api\ZaloNotificationSmsController::class, 'notifyZNS'])->name('verified_zns.notify_zns');

Route::get('test/redis', [\App\Http\Controllers\TestController::class, 'redisConnect']);

Route::get('error/500', function () {
    return view('errors/500');
})->name('server_error');
Route::fallback(function () {
    return view('errors/404');
})->name('not_found');

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'log_request'], function () {
    Route::group(['prefix' => 'public'], function () {
        Route::any('sms-reserve', [\App\Http\Controllers\ApiV2\SmsReserveController::class, 'store']);


        Route::get('payments', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'store']);
//        Route::post('payments', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'store']);
//        Route::put('payments', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'update']);
    });
    Route::post('sms-reserve/on-receive', [\App\Http\Controllers\ApiV2\SmsReserveController::class, 'onReceive'])
        ->middleware('auth:third_party');
    Route::get('school-tour', [\App\Http\Controllers\ApiV2\SchoolTourController::class,'store']);
    Route::post('flt-easy-ielts', [\App\Http\Controllers\ApiV2\FltEasyIeltsController::class,'store']);
    Route::get('payments', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'index']);
    Route::get('payments/{code}', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'show']);
    Route::put('payments/{code}', [\App\Http\Controllers\ApiV2\InvoiceController::class, 'update']);
});

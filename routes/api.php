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

Route::group(['prefix' => 'v1', 'middleware' => ['log_request','throttle:api']], function () {
    Route::apiResource('branchs', \App\Http\Controllers\Api\BranchController::class)->middleware('auth:third_party');
    Route::get('exportContactExamsNull', [\App\Http\Controllers\Api\ContactLeadController::class, 'exportContactExamNull'])->middleware('auth:third_party');

    Route::apiResource('contacts', \App\Http\Controllers\Api\ContactLeadController::class)->middleware('auth:third_party');

    Route::apiResource('departments', \App\Http\Controllers\Api\DepartmentController::class)
        ->middleware('auth:third_party');
    Route::apiResource('landing-pages', \App\Http\Controllers\Api\LandingPageController::class)
        ->middleware('auth:third_party');
    Route::apiResource('campaigns', \App\Http\Controllers\Api\CampaignController::class)
        ->middleware('auth:third_party');
    Route::apiResource('traffics', \App\Http\Controllers\Api\TrafficController::class)->middleware('auth:third_party');
    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class)->middleware('auth:third_party');
    Route::apiResource('api-partners', \App\Http\Controllers\Api\ApiPartnersController::class)
        ->middleware('auth:third_party');

    Route::get('coupon/check', [\App\Http\Controllers\Api\CouponController::class, 'checkCoupon'])
        ->middleware('auth:third_party');
    Route::get('coupon', [\App\Http\Controllers\Api\CouponController::class, 'index'])->middleware('auth:third_party');
    Route::post('coupon', [\App\Http\Controllers\Api\CouponController::class, 'storage'])
        ->middleware('auth:third_party');

    Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'index']);

    Route::apiResource('landing-page-infos', \App\Http\Controllers\Api\LandingPageInfoController::class)->middleware('auth:third_party');
    Route::apiResource('landing-page-trackings',\App\Http\Controllers\Api\LandingPageTrackingController::class)->middleware('auth:third_party');

    Route::apiResource('contact-exams', \App\Http\Controllers\Api\ContactExamController::class)->middleware('auth:third_party');
    Route::get('contact-exam/detail', [\App\Http\Controllers\Api\ContactExamController::class, 'detailContactExams'])->middleware('auth:third_party');
    Route::apiResource('contact-exam-logs', \App\Http\Controllers\Api\ContactExamLogController::class)->middleware('auth:third_party');

    Route::apiResource('email-saves', \App\Http\Controllers\Api\EmailSaveController::class)->middleware('auth:third_party');

    Route::apiResource('pgw-partner',\App\Http\Controllers\Api\PGW\PgwPartnerController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-orders', \App\Http\Controllers\Api\PGW\PgwOrderController::class)->middleware('auth:third_party');
    Route::post('pgw-orders-updatePaid',[\App\Http\Controllers\Api\PGW\PgwOrderController::class,'updateOrderPaid'])->middleware('auth:third_party');
    Route::post('pgw-orders-updateStatusClient',[\App\Http\Controllers\Api\PGW\PgwOrderController::class,'updateStatusClient'])->middleware('auth:third_party');

    Route::get('pgw-statistical',[\App\Http\Controllers\Api\PGW\PgwStatisticalController::class,'statistical'])->middleware('auth:third_party');
    Route::get('pgw-statistical-merchant',[\App\Http\Controllers\Api\PGW\PgwStatisticalController::class,'statisticalMerchant'])->middleware('auth:third_party');
    Route::get('pgw-statistical-revenue',[\App\Http\Controllers\Api\PGW\PgwStatisticalController::class,'statisticalRevenue'])->middleware('auth:third_party');
    Route::get('pgw-statistical-merchant-revenue',[\App\Http\Controllers\Api\PGW\PgwStatisticalController::class,'statisticalMerchantRevenue'])->middleware('auth:third_party');

    Route::apiResource('pgw-order-refund',\App\Http\Controllers\Api\PGW\PgwOrderRefundController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-order-details',\App\Http\Controllers\Api\PGW\PgwOrderDeatailController::class)->middleware('auth:third_party');
    Route::put('pgw-order-details-multiple',[\App\Http\Controllers\Api\PGW\PgwOrderDeatailController::class,'updateMultiple'])->middleware('auth:third_party');


    Route::apiResource('pgw-payment-merchant',\App\Http\Controllers\Api\PGW\PgwPaymentMerchantController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-partner-resgistri-merchant',\App\Http\Controllers\Api\PGW\PgwPartnerResgistriMerchantController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-partner-registri-banking',\App\Http\Controllers\Api\PGW\PgwPartnerRegistriBankingController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-banking-list',\App\Http\Controllers\Api\PGW\PgwBankingListController::class)->middleware('auth:third_party');
    Route::apiResource('pgw-payment-request',\App\Http\Controllers\Api\PGW\PgwPaymentRequestController::class)->middleware('auth:third_party');

    Route::apiResource('sys-users',\App\Http\Controllers\Api\SYS\SysUserController::class)->middleware('auth:third_party');
    Route::apiResource('sys-groups',\App\Http\Controllers\Api\SYS\SysGroupController::class)->middleware('auth:third_party');
    Route::apiResource('sys-users-groups',\App\Http\Controllers\Api\SYS\SysUserGroupController::class)->middleware('auth:third_party');
    Route::apiResource('sys-users-landingpages',\App\Http\Controllers\Api\SYS\SysUserLandingpageController::class)->middleware('auth:third_party');
    Route::apiResource('sys-modules',\App\Http\Controllers\Api\SYS\SysModuleController::class)->middleware('auth:third_party');

    Route::post('sys-permissions/scan',[\App\Http\Controllers\Api\SYS\SysPermissionController::class,'scanPermission'])->middleware('auth:third_party');
    Route::apiResource('sys-permissions',\App\Http\Controllers\Api\SYS\SysPermissionController::class)->middleware('auth:third_party');

    Route::apiResource('sys-group-permissions',\App\Http\Controllers\Api\SYS\SysGroupPermissionController::class)->middleware('auth:third_party');
    Route::apiResource('sys-user-landing-pages',\App\Http\Controllers\Api\SYS\SysUserLandingpageController::class)->middleware('auth:third_party');

    Route::post('send-sms',[\App\Http\Controllers\Api\LogSendSmsController::class,'sendSms'])->middleware('auth:third_party');
    Route::apiResource('message-templates',\App\Http\Controllers\Api\MessageTemplateController::class)->middleware('auth:third_party');

    Route::apiResource('emails-templates',\App\Http\Controllers\Api\EmailTemplateController::class)->middleware('auth:third_party');
    Route::apiResource('active-codes',\App\Http\Controllers\Api\ActiveCodeController::class)->middleware('auth:third_party');


    Route::apiResource('login-google',\App\Http\Controllers\Api\LoginGoogleController::class);

    Route::get('check-active-code',[\App\Http\Controllers\Api\PGW\PgwOrderController::class, 'checkActiveCode']);
    Route::get('getOrderByBillCode',[\App\Http\Controllers\Api\PGW\PgwOrderController::class, 'getOrderByBillCode']);

    /***
     * Phần quay số
     */
    Route::group(['prefix' => 'wheels'], function () {
        Route::get('infos', [\App\Http\Controllers\Api\Gifts\RandomGiftController::class, 'getInfo']);
        Route::get('run-wheel', [\App\Http\Controllers\Api\Gifts\RandomGiftController::class, 'runWheel']);
        Route::get('run-wheel-fahasa', [\App\Http\Controllers\Api\Gifts\RandomGiftController::class, 'runWheelFahasaNew'])->middleware('throttle:wheels');
        Route::apiResource('ticket', \App\Http\Controllers\Api\Gifts\TicketController::class)->middleware('throttle:wheels');
        Route::get('gift-coupon', [\App\Http\Controllers\Api\Gifts\GiftCouponController::class, 'index']);
        Route::get('gift-random-contact', [\App\Http\Controllers\Api\Gifts\RandomGiftContactController::class, 'index']);
    });

    /**
     * Payment gateway
    */
    Route::group(['prefix' => 'pgw'], function () {
        Route::post('pay', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'pay']);
        Route::post('checkbill', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'checkBill']);
        Route::post('{paymentMerchantCode}/getbill', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'getBill']);
        Route::post('{paymentMerchantCode}/paybill', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'payBill']);
        Route::get('{paymentMerchantCode}/paybill', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'payBill']);
        Route::post('msb/callback', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'callback']);
        Route::post('changeStatusCancelOrder', [\App\Http\Controllers\Api\PGW\PgwPaymentController::class, 'changeStatusOrderCancel']);
    });

    Route::group(['prefix' => 'zns'],function (){
        Route::get('callback', [\App\Http\Controllers\Api\ZaloNotificationSmsController::class, 'callback']);
        Route::get('getAccessToken', [\App\Http\Controllers\Api\ZaloNotificationSmsController::class, 'redirectOAURL']);
        Route::post('sendZns', [\App\Http\Controllers\Api\ZaloNotificationSmsController::class, 'sendZns'])->middleware('auth:third_party');
        Route::get('activeContact', [\App\Http\Controllers\Api\ZaloNotificationSmsController::class,'activeContact']);
    });

    Route::group(['prefix' => 'public'], function () {
        Route::get('contacts', [\App\Http\Controllers\Api\ContactLeadController::class,'store']);
        Route::get('pgw-orders', [\App\Http\Controllers\Api\PGW\PgwOrderController::class, 'store']);
        Route::post('payment/callback', [\App\Http\Controllers\Api\PGW\PgwOrderController::class, 'callback']);
    });
});
Route::fallback(function () {
    return view('errors/404');
});


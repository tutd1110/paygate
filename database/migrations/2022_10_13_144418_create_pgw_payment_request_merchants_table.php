<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPaymentRequestMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_payment_request_merchants', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_request_id');
            $table->integer('merchant_id');
            $table->integer('banking_id');
            $table->integer('order_client_id');
            $table->string('vpc_MerchTxnRef')->comment('Mã payment gateway gửi sang cổng thanh toán');
            $table->enum('paid_status', ['success','unsuccess'])->default('unsuccess');
            $table->string('description')->nullable();
            $table->integer('query_time')->comment('Số lần chạy query để kiểm tra lại giao dich')->nullable();
            $table->enum('transaction_status', ['Y','N'])->default('N')->comment('Trạng thái của giao dịch để biết xem đã phát sinh giao dịch chưa');
            $table->string('respon_code',25)->comment('Mã giao dịch = 0 là thành công còn lại là mã lỗi của cổng thanh toán')->nullable()->default(null);
            $table->string('remote_address')->comment('Id của server gửi sang cổng thanh toán')->nullable();
            $table->string('web_browse')->comment('trình duyệt người dùng sử dụng')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pgw_payment_request_merchant');
    }
}

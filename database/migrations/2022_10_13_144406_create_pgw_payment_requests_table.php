<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPaymentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('partner_code',25)->comment('Mã của clent hoặc mã của đối tác cần thanh toán');
            $table->integer('order_client_id')->comment('Mã hóa đơn của client gửi sang để thanh toán nếu có');
            $table->integer('merchant_id');
            $table->integer('banking_id')->nullable();
            $table->string('payment_code',25)->comment('Mã của payment gateway sinh ra để làm việc với cổng thanh toán');
            $table->integer('transsion_id')->comment('Mã của cổng thanh toán trả về (Mã giao dịch)')->nullable();
            $table->decimal('payment_value',19,4)->comment('Số tiền phải thanh toán');
            $table->decimal('total_pay',19,4)->comment('Số tiền thực tế người dùng thanh toán')->nullable();
            $table->enum('paid_status', ['success','unsuccess'])->default('unsuccess');
            $table->string('url_return_true')->nullable();
            $table->string('url_return_false')->nullable();
            $table->string('url_return_api')->nullable();
            $table->string('custom')->comment('Lưu mảng json dữ liệu tùy chọn của client khi gửi lên và muốn nhận lại sau phát sinh thanh toán')->nullable();
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
        Schema::dropIfExists('pgw_payment_request');
    }
}

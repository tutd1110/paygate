<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPaymentPayLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_payment_pay_logs', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_invoice')->comment('Mã thanh toán của cổng thanh toán trả về');
            $table->string('order_id');
            $table->integer('merchant_id');
            $table->integer('banking_id');
            $table->string('description')->comment('Thông tin thanh toán mà merchant trả về')->nullable();
            $table->decimal('paid_value',19,4);
            $table->enum('paid_status', ['success','unsuccess'])->default('unsuccess');
            $table->integer('payment_request_merchant_id');
            $table->enum('sync',['true','false'])->default('false');
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
        Schema::dropIfExists('pgw_payment_pay_log');
    }
}

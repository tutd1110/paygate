<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmTimePushToLandingpages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpages', function (Blueprint $table) {
            $table->integer('send_sms_invoice_delay')->default(0)->after('api_info')
                ->comment('Thời gian contact chưa thanh toán từ lúc tạo bill đến khi gửi tin nhắn = 0 là không gửi');
            $table->integer('push_crm_invoice_delay')->default(0)->after('send_sms_invoice_delay')
                ->comment('Thời gian contact chưa thanh toán từ lúc tạo bill đến khi push vao crm = 0 là không gửi');

        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('merchant_code', 35)->default('')->nullable()->after('code')->comment('Tên cổng thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landingpages', function (Blueprint $table) {
            //
        });
    }
}

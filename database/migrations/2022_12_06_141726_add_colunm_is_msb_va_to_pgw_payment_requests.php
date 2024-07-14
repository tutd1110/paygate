<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmIsMsbVaToPgwPaymentRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_payment_requests', function (Blueprint $table) {
            $table->enum('is_msb_va', ['yes','no'])->default('no')->after('custom');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pgw_payment_request', function (Blueprint $table) {
            //
        });
    }
}

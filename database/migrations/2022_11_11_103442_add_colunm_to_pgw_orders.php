<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToPgwOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            $table->string('merchant_code',10)->after('is_api');
            $table->string('banking_code',10)->after('merchant_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnActiveCodeToPgwOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            $table->string('active_code')->default(null)->comment('Mã kích hoạt')->after('code_reverse')->nullable();
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

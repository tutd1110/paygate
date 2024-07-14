<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StatusChangeColunmPgwOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            $table->enum('status',['new','processing','waiting','paid','refund','fail'])->default('new')->after('quantity');
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

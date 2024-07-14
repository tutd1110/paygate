<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_order_refunds', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('landing_page_id');
            $table->string('partner_code',25);
            $table->integer('refund_value');
            $table->string('description')->nullable();
            $table->enum('status', ['request','refused','appoved','finish'])->default('request');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('pgw_order_refund');
    }
}

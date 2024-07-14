<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPaymentMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_payment_merchants', function (Blueprint $table) {
            $table->id();
            $table->string('code',25);
            $table->string('name');
            $table->string('thumb_path')->nullable();
            $table->enum('status', ['active','inactive'])->default('inactive');
            $table->integer('sort')->nullable();
            $table->string('description')->nullable();
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
        Schema::dropIfExists('pgw_payment_merchant');
    }
}

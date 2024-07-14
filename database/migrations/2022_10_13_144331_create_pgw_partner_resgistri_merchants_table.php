<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPartnerResgistriMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_partner_resgistri_merchants', function (Blueprint $table) {
            $table->id();
            $table->string('partner_code',25);
            $table->integer('payment_merchant_id');
            $table->integer('sort')->nullable();
            $table->text('business');
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
        Schema::dropIfExists('pgw_partner_resgistri_merchant');
    }
}

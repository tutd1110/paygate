<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAutoCancelOrderToLandingpages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpages', function (Blueprint $table) {
            $table->integer('auto_cancel_order')->after('push_crm_invoice_delay')->default(0);
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

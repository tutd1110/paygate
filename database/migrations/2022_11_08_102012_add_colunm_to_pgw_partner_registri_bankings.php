<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToPgwPartnerRegistriBankings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_partner_registri_bankings', function (Blueprint $table) {
            $table->enum('type',['topup','billing'])->default('billing')->after('business');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pgw_partner_registri_bankings', function (Blueprint $table) {

        });
    }
}

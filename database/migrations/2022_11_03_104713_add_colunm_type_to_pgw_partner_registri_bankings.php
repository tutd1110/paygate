<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmTypeToPgwPartnerRegistriBankings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_partner_registri_bankings', function (Blueprint $table) {
            $table->string('banking_list_id')->after('code');
            $table->enum('type',['topup','transfer'])->default('transfer')->after('business');
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
            //
        });
    }
}

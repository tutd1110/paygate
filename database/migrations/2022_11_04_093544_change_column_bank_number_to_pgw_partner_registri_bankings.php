<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnBankNumberToPgwPartnerRegistriBankings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pgw_partner_registri_bankings', function (Blueprint $table) {
            $table->string('bank_number')->comment('Số tài khoản')->change();
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

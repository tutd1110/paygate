<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToContactLeadsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->enum('crm_type', [
                'TH',
                'THCS',
                'THPT',
                'SPEAKUP',
                'SG',
            ])->after('class');

            $table->enum('action', [
               'register',
               'learn',
               'pre_reserve',
               'reserve',
               'give_package',
               'to_cart',
               'give_multiple_package',
            ])->after('class')->nullable();
            $table->string('address')->after('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_leads', function (Blueprint $table) {

        });
    }
}

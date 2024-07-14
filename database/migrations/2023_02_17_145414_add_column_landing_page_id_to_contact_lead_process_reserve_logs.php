<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLandingPageIdToContactLeadProcessReserveLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_lead_process_reserve_logs', function (Blueprint $table) {
            $table->integer('landing_page_id')->nullable()->after('contact_lead_process_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_lead_process_reserve_logs', function (Blueprint $table) {
        });
    }
}

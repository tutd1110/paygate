<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIdHasFromReserveFormToContactLeadProcessReserveLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_lead_process_reserve_logs', function (Blueprint $table) {
            $table->boolean('is_has_from_reserve_form')->default(1)->after('contact_lead_process_id');
        });

        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->boolean('is_has_from_reserve_form')->default(1)->after('landing_page_id');
        });

        Schema::table('contact_leads', function (Blueprint $table) {
            $table->boolean('is_has_from_reserve_form')->default(1)->after('landing_page_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_form_to_contact_lead_process_reserve_logs', function (Blueprint $table) {
            $table->boolean('is_has_from_reserve_form')->default(1)->after('landing_page_id');
        });
    }
}

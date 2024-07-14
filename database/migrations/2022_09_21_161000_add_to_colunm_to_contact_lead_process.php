<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToColunmToContactLeadProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->string('gender')->nullable()->default("Nam")->after('register_ip');
            $table->string('birth_day')->nullable()->default(null)->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_lead_process', function (Blueprint $table) {
            //
        });
    }
}

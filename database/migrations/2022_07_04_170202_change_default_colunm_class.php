<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultColunmClass extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->integer('class')->nullable()->change()->default(null);
        });
        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->integer('class')->nullable()->change()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditContactLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->boolean('scan')->default(0)->change();
            $table->boolean('is_duplicate')->default(0)->change();
            $table->boolean('is_email_duplicate')->default(0)->change();
            $table->boolean('is_phone_duplicate')->default(0)->change();
            $table->boolean('is_active')->default(1)->change();
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

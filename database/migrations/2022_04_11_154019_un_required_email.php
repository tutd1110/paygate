<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnRequiredEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->integer('class')->default(0)->nullable()->change();
            $table->boolean('is_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_email_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_phone_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_phone_duplicate')->default(0)->nullable()->change();
        });

        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->integer('class')->default(0)->nullable()->change();
            $table->boolean('is_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_email_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_phone_duplicate')->default(0)->nullable()->change();
            $table->boolean('is_phone_duplicate')->default(0)->nullable()->change();
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContactLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('landing_page_id');
            $table->integer('campaign_id');
            $table->integer('hocmai_id');
            $table->integer('sashi_id');
            $table->integer('olm_id');
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('email', 255);
            $table->tinyInteger('class');
            $table->text('description');
            $table->tinyInteger('scan');
            $table->string('utm_medium')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->tinyInteger('is_duplicate');
            $table->tinyInteger('is_email_duplicate');
            $table->tinyInteger('is_phone_duplicate');
            $table->tinyInteger('is_active');
            $table->string('register_ip');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
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

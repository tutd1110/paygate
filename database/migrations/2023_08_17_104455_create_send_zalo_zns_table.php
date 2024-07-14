<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendZaloZnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sent_zalo_zns', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id')->default(0);
            $table->integer('contact_lead_processs_id')->default(0);
            $table->text('headers')->default(null)->nullable();
            $table->string('to_phone')->default(null)->nullable();
            $table->integer('template_id')->default(0);
            $table->string('template_data')->default(null)->nullable();
            $table->enum('status',['create','sent','sent_error'])->default('create');
            $table->text('response')->default(null)->nullable();
            $table->string('sent_time')->nullable();
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
        Schema::dropIfExists('send_zalo_zns');
    }
}

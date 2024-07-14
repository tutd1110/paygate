<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateZaloZnsLandingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_zalo_zns_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id');
            $table->integer('template_id');
            $table->string('template_data');
            $table->enum('status',['active','inactive']);
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
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
        Schema::dropIfExists('template_zalo_zns_landing_pages');
    }
}

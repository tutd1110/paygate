<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingPageInfo extends Migration
{
    public function up()
    {
        Schema::create('landing_page_info', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id')->default(0);
            $table->string('transfer_syntax')->default(null);
            $table->string('sms_content_paid')->default(null);
            $table->string('sms_content_remind')->default(null);
            $table->string('email_content_paid')->default(null);
            $table->string('email_content_remind')->default(null);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
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
        Schema::dropIfExists('landing_page_info');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactLeadProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_lead_process', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default(0);
            $table->integer('landing_page_id')->default(0);
            $table->integer('campaign_id')->default(0);
            $table->integer('crm_id')->default(0);
            $table->integer('sashi_id')->default(0);
            $table->integer('olm_id')->default(0);
            $table->string('full_name')->default('');
            $table->string('phone')->default('');
            $table->string('address')->default('')->nullable();
            $table->string('email')->default('');
            $table->tinyInteger('class')->default(0);
            $table->enum('crm_type', ['TH','THCS','THPT','SPEAKUP','SG'])->nullable()->default(null);
            $table->enum('action', ['register','learn','pre_reserve','reserve','give_package','to_cart','give_multiple_package'])->default(null)->nullable();
            $table->text('description')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->boolean('is_duplicate')->default(0);
            $table->boolean('is_email_duplicate')->default(0);
            $table->boolean('is_phone_duplicate')->default(0);
            $table->string('register_ip')->default('');
            $table->integer('created_by')->default(0);
            $table->integer('is_active')->default(0);
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
        Schema::dropIfExists('contact_lead_process');
    }
}

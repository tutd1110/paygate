<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactPushCrmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_push_partners', function (Blueprint $table) {
            $table->id();
            $table->integer('contact_lead_id')->nullable();
            $table->integer('api_partner_id')->nullable();
            $table->integer('contact_lead_process_id')->nullable();
            $table->integer('crm_id')->comment('crm id bên đối tác')->nullable();
            $table->integer('partner_contact_id')->nullable()->comment('id contact bên bảng đối tác ');
            $table->integer('landing_page_contact_id')->nullable()->comment('id bên bảng đối tác');
            $table->integer('reserve_contact_id')->nullable()->comment('id trên bảng reserve contact đối tác');
            $table->text('extend_info')->nullable();
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
        Schema::dropIfExists('contact_push_partners');
    }
}

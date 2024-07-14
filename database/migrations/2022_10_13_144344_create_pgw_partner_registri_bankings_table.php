<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwPartnerRegistriBankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_partner_registri_bankings', function (Blueprint $table) {
            $table->id();
            $table->string('code',25);
            $table->string('name');
            $table->string('description');
            $table->string('partner_code',25);
            $table->string('thumb_path')->nullable();
            $table->string('owner')->comment('Tên của chủ tài khoản');
            $table->integer('bank_number')->comment('Số tài khoản');
            $table->string('branch')->comment('Chi nhánh của tài khoản')->nullable();
            $table->text('business')->comment('Thông tin kết nối đến ngân hàng');
            $table->enum('status', ['active','inactive'])->default('inactive');
            $table->integer('sort')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('pgw_partner_registri_banking');
    }
}

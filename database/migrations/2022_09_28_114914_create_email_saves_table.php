<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailSavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_saves', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id');
            $table->integer('contact_id');
            $table->string('from_email',255);
            $table->string('from_name',255);
            $table->string('to_email',255);
            $table->string('to_name',255);
            $table->string('cc_email')->default(null)->nullable();
            $table->string('bcc_email')->default(null)->nullable();
            $table->string('reply_to')->default(null)->nullable();
            $table->string('subject',255)->default(null)->nullable()->comment('Đối tượng');
            $table->text('content',1500)->default(null)->nullable()->comment('Nội dung');
            $table->string('file_attach')->default(null)->nullable();
            $table->integer('send_time')->nullable()->default(0)->comment('Thời gian gửi email');
            $table->string('status')->nullable()->default('waiting');
            $table->integer('send_error')->nullable()->default(0);
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
        Schema::dropIfExists('email_saves');
    }
}

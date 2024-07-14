<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SmsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone')->default('');
            $table->string('sms_content')->default('');
            $table->bigInteger('object_id')->default(0);
            $table->string('object_instance')->default('');
            $table->enum('sent_status', [
                'create',
                'sent',
                'sent_error'
            ])->default('sent');
            $table->text('data')->default('');
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

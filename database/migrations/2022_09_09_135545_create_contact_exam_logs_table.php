<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactExamLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_exam_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('contact_id');
            $table->integer('session_id');
            $table->integer('question_id');
            $table->string('question_name');
            $table->string('result')->default(null)->nullable();
            $table->integer('score')->default(null)->nullable();
            $table->integer('time');
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
        Schema::dropIfExists('contact_exam_logs');
    }
}

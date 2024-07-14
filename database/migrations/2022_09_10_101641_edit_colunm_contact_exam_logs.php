<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColunmContactExamLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_exam_logs', function (Blueprint $table) {
            $table->integer('contact_id')->default(0)->change();
            $table->integer('session_id')->default(0)->change();
            $table->integer('question_id')->default(0)->change();
            $table->string('question_name',10)->default(null)->change();
            $table->string('result',10)->default(null)->nullable()->change();
            $table->integer('score')->default(0)->nullable()->change();
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

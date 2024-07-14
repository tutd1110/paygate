<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColunmContactExams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_exams', function (Blueprint $table) {
            $table->integer('total_question')->default(0)->change();
            $table->integer('total_score')->default(0)->change();
            $table->integer('total_time')->default(0)->change();
            $table->integer('number')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_exams');
    }
}

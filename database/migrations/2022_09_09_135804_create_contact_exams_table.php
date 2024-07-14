<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_exams', function (Blueprint $table) {
            $table->id();
            $table->integer('contact_id');
            $table->integer('total_question');
            $table->integer('total_score');
            $table->boolean('is_done')->default(false);
            $table->integer('total_time');
            $table->integer('number')->default(1);
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
        Schema::dropIfExists('contact_exams');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('url');
            $table->integer('status_code')->nullable();
            $table->text('headers')->nullable();
            $table->text('option')->nullable();
            $table->text('response')->nullable();
            $table->string('method', 10)->nullable();
            $table->integer('is_success')->default(0)->nullable();
            $table->text('exception_info')->nullable();
            $table->string('file')->nullable();
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
        Schema::dropIfExists('request_logs');
    }
}

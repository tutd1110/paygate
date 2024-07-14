<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingpagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landingpages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->integer('branch_id');
            $table->integer('department_id');
            $table->string('domain_name');
            $table->text('description');
            $table->string('server_id', 50);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->tinyInteger('status');
            $table->string('developer');
            $table->tinyInteger('type');
            $table->text('api_info')->comment('json_type');
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('landingpages');
    }
}

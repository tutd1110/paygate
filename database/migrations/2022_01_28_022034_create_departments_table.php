<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', '255');
            $table->string('name', '255');
            $table->text('description')->nullable();
            $table->integer('quota')->default(0)->comment(' Số lượng landing page được tạo của phòng ban');
            $table->tinyInteger('status');
            $table->tinyInteger('is_delete');
            $table->integer('branch_id');
            $table->integer('branch_name');
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
        Schema::dropIfExists('departments');
    }
}

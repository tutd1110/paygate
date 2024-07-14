<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmStatusToRandomGifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('random_gifts', function (Blueprint $table) {
            $table->enum('type',['product','coupon'])->default('product')->after('rate');
            $table->enum('status',['active','inactive'])->default('active')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('random_gifts', function (Blueprint $table) {
            //
        });
    }
}

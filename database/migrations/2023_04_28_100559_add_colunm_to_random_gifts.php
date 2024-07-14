<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToRandomGifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('random_gifts', function (Blueprint $table) {
            $table->text('description')->nullable()->after('full_name');
            $table->string('thumb')->nullable()->after('description');
            $table->integer('rate')->after('quantity_use');
            $table->enum('type',['product','coupon'])->after('rate');
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

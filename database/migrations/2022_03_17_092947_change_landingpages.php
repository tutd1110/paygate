<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLandingpages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpages', function (Blueprint $table) {
            $table->renameColumn('coupon_name', 'event');
            $table->dateTime('start_time_coupon')->nullable()->after('type');
            $table->dateTime('end_time_coupon')->nullable()->after('start_time_coupon');
            $table->dateTime('allow_reserve_start_time')->nullable()->after('end_time_coupon');
            $table->dateTime('register_start_time')->nullable()->after('allow_reserve_start_time');
            $table->dateTime('register_end_time')->nullable()->after('register_start_time');
            $table->integer('olm_id')->nullable()->default(0)->after('register_end_time');
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

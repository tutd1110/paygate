<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColunmToLandingPageTrackings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_page_trackings', function (Blueprint $table) {
            $table->text('header_bottom')->nullable()->change();
            $table->text('body')->nullable()->change();
            $table->text('body_bottom')->nullable()->change();
            $table->text('footer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landing_page_trackings', function (Blueprint $table) {
            //
        });
    }
}

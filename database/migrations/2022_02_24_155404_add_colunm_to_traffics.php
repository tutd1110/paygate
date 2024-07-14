<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToTraffics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('traffics')->truncate();
        Schema::table('traffics', function (Blueprint $table) {
            $table->string('uri')->after('cookie_id')->nullable();
            $table->string('query_string')->after('cookie_id')->nullable();
            $table->string('session_id')->after('cookie_id');

            $table->unique(['uri', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('traffics', function (Blueprint $table) {
            //
        });
    }
}

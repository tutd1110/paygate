<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToActiveCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE active_code MODIFY COLUMN used ENUM('yes', 'no') DEFAULT 'no'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `active_code` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `active_code` CHANGE `updated_at` `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('active_code', function (Blueprint $table) {
            //
        });
    }
}

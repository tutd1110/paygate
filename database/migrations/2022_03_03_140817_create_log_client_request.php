<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogClientRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /***
         * id` bigint(20) NOT NULL AUTO_INCREMENT,
        `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
        `status_code` int(11) DEFAULT NULL,
        `headers` text COLLATE utf8mb4_unicode_ci,
        `option` text COLLATE utf8mb4_unicode_ci,
        `response` text COLLATE utf8mb4_unicode_ci,
        `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `is_success` int(11) DEFAULT '0',
        `exception_info` text COLLATE utf8mb4_unicode_ci,
        `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,

         */
        Schema::create('log_client_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 2000)->nullable();
            $table->integer('status_code')->nullable();
            $table->text('header')->nullable();
            $table->text('data')->nullable();
            $table->string('method')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('log_client_requests');
    }
}

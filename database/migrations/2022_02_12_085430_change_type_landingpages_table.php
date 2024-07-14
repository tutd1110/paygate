<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypeLandingpagesTable extends Migration
{
    public function __construct()
    {
//        try {
//            \Doctrine\DBAL\Types\Type::hasType('enum') ?: \Doctrine\DBAL\Types\Type::addType('enum', \Doctrine\DBAL\Types\StringType::class);
//        } catch (\Exception $exception) {
//            Log::info($exception->getMessage());
//        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE landingpages MODIFY COLUMN type ENUM(
                'basic',
                'advanced')  DEFAULT 'basic'");

        DB::statement("ALTER TABLE landingpages MODIFY COLUMN status ENUM(
                'new',
                'processing',
                'waiting',
                'approved',
                'expired') DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

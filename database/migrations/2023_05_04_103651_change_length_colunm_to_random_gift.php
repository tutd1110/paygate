<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLengthColunmToRandomGift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        \Illuminate\Support\Facades\DB::getDoctrineSchemaManager()
            ->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'string');
    }
    public function up()
    {
        Schema::table('random_gifts', function (Blueprint $table) {
            $table->integer('is_default')->change();
            $table->string('name',255)->change();
            $table->string('full_name',255)->change();
            $table->text('description')->nullable()->change();
            $table->text('thumb')->nullable()->change();
            $table->integer('created_by')->change();
            $table->integer('updated_by')->change();

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

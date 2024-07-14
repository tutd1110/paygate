<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\{StringType, Type};

class ChangeColunmStatusToPgwOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        Type::hasType('enum') ?: Type::addType('enum', StringType::class);
    }
    public function up()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            $table->enum('status', ['new','processing','waiting','paid','refund','fail','cancel','expired'])->default('new')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pgw_orders', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('partner_code',50)->nullable()->after('email');
            $table->string('address')->nullable()->after('partner_code');
            $table->enum('owner',['yes','no'])->default('no')->after('address');
            $table->enum('status',['active','inactive','deleted'])->default('active')->after('owner');
            $table->integer('created_by')->nullable()->after('phone');
            $table->integer('updated_by')->nullable()->after('created_by');
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
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}

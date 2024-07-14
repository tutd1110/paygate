<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRandomGifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('random_gifts', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id');
            $table->string('name', 255);
            $table->integer('quantity');
            $table->integer('quantity_use');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::create('random_gift_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id');
            $table->integer('contact_id');
            $table->integer('gift_id');
            $table->integer('user_id');
            $table->integer('created_by');
            $table->integer('updated_by');
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
    }
}

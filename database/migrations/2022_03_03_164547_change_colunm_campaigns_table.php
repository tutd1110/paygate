<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColunmCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('description')->nullable()->default('')->change();
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->integer('adverting_budget')->nullable()->default(0)->change();
            $table->integer('amount_spent')->nullable()->default(0)->change();
            $table->integer('is_active')->default(1)->nullable()->change();
            $table->integer('created_by')->nullable()->default(0)->change();
            $table->integer('updated_by')->nullable()->default(0)->change();
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

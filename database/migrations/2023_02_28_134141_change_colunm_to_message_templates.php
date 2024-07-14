<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColunmToMessageTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('code','50')->change();
            $table->string('template_name')->nullable()->change();
            $table->integer('parent_id')->nullable()->default(0)->change();
            $table->integer('landing_page_id')->nullable()->default(0)->change();
            $table->string('bind_param')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_templates', function (Blueprint $table) {
            //
        });
    }
}

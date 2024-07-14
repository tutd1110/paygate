<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUtmIdToMultipleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('traffics', function (Blueprint $table) {
            $table->integer('utm_campaign_id')->after('utm_source')->nullable()->default(0);
            $table->integer('utm_content_id')->after('utm_campaign_id')->nullable()->default(0);
            $table->integer('utm_creator_id')->after('utm_content_id')->nullable()->default(0);
            $table->integer('utm_medium_id')->after('utm_creator_id')->nullable()->default(0);
            $table->integer('utm_source_id')->after('utm_medium_id')->nullable()->default(0);
            $table->integer('utm_term_id')->after('utm_source_id')->nullable()->default(0);
        });

        Schema::table('contact_leads', function (Blueprint $table) {
            $table->integer('utm_campaign_id')->after('utm_source')->nullable()->default(0);
            $table->integer('utm_content_id')->after('utm_campaign_id')->nullable()->default(0);
            $table->integer('utm_creator_id')->after('utm_content_id')->nullable()->default(0);
            $table->integer('utm_medium_id')->after('utm_creator_id')->nullable()->default(0);
            $table->integer('utm_source_id')->after('utm_medium_id')->nullable()->default(0);
            $table->integer('utm_term_id')->after('utm_source_id')->nullable()->default(0);
        });

        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->integer('utm_campaign_id')->after('utm_source')->nullable()->default(0);
            $table->integer('utm_content_id')->after('utm_campaign_id')->nullable()->default(0);
            $table->integer('utm_creator_id')->after('utm_content_id')->nullable()->default(0);
            $table->integer('utm_medium_id')->after('utm_creator_id')->nullable()->default(0);
            $table->integer('utm_source_id')->after('utm_medium_id')->nullable()->default(0);
            $table->integer('utm_term_id')->after('utm_source_id')->nullable()->default(0);
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

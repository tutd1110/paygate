<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('utm_medium')->default('direct')->nullable()->change()->index('unique_medium');
            $table->string('utm_source')->default('direct')->nullable()->change()->index('unique_source');
            $table->string('utm_campaign')->default('direct')->nullable()->change()->index('unique_campaign');
        });

        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->string('utm_medium')->default('direct')->nullable()->change()->index('unique_medium');
            $table->string('utm_source')->default('direct')->nullable()->change()->index('unique_source');
            $table->string('utm_campaign')->default('direct')->nullable()->change()->index('unique_campaign');
        });

        Schema::table('traffics', function (Blueprint $table) {
            $table->string('utm_medium')->default('direct')->nullable()->change()->index('unique_medium');
            $table->string('utm_source')->default('direct')->nullable()->change()->index('unique_source');
            $table->string('utm_campaign')->default('direct')->nullable()->change()->index('unique_campaign');
        });
        \App\Models\ContactLead::where('utm_campaign', null)->update([
            'utm_campaign' => 'direct',
        ]);
        \App\Models\ContactLead::where('utm_source', null)->update([
            'utm_source' => 'direct',
        ]);
        \App\Models\ContactLead::where('utm_medium', null)->update([
            'utm_medium' => 'direct',
        ]);

        \App\Models\ContactLeadProcess::where('utm_campaign', null)->update([
            'utm_campaign' => 'direct',
        ]);
        \App\Models\ContactLeadProcess::where('utm_source', null)->update([
            'utm_source' => 'direct',
        ]);
        \App\Models\ContactLeadProcess::where('utm_medium', null)->update([
            'utm_medium' => 'direct',
        ]);

        \App\Models\Traffic::where('utm_campaign', null)->update([
            'utm_campaign' => 'direct',
        ]);
        \App\Models\Traffic::where('utm_source', null)->update([
            'utm_source' => 'direct',
        ]);
        \App\Models\Traffic::where('utm_medium', null)->update([
            'utm_medium' => 'direct',
        ]);



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

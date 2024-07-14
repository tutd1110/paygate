<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUtmCampaign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('utm_campaign')->default('none')->nullable()->change();
        });

        Schema::table('contact_lead_process', function (Blueprint $table) {
            $table->string('utm_campaign')->default('none')->nullable()->change();
        });

        Schema::table('traffics', function (Blueprint $table) {
            $table->string('utm_campaign')->default('none')->nullable()->change();
        });

        \App\Models\ContactLead::where('utm_campaign', 'direct')->update([
            'utm_campaign' => 'none',
        ]);


        \App\Models\ContactLeadProcess::where('utm_campaign', 'direct')->update([
            'utm_campaign' => 'none',
        ]);


        \App\Models\Traffic::where('utm_campaign', 'direct')->update([
            'utm_campaign' => 'none',
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

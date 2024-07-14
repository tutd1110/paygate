<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNullInSomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\ContactLead::where('utm_campaign', null)->update([
            'utm_campaign' => 'none',
        ]);
        \App\Models\ContactLead::where('utm_source', null)->update([
            'utm_source' => 'direct',
        ]);
        \App\Models\ContactLead::where('utm_medium', null)->update([
            'utm_medium' => 'direct',
        ]);

        \App\Models\ContactLeadProcess::where('utm_campaign', null)->update([
            'utm_campaign' => 'none',
        ]);
        \App\Models\ContactLeadProcess::where('utm_source', null)->update([
            'utm_source' => 'direct',
        ]);
        \App\Models\ContactLeadProcess::where('utm_medium', null)->update([
            'utm_medium' => 'direct',
        ]);

        \App\Models\Traffic::where('utm_campaign', null)->update([
            'utm_campaign' => 'none',
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

    }
}

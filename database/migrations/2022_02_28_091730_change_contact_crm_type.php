<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeContactCrmType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $contactLeadTable = (new \App\Models\ContactLead())->getTable();
        \Illuminate\Support\Facades\DB::unprepared("ALTER TABLE `{$contactLeadTable}`
MODIFY COLUMN `crm_type` enum('TH','THCS','THPT','SPEAKUP','SG') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `action_status`");
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

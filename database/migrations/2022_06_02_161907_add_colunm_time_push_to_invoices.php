<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColunmTimePushToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_must_push_contact_unpaid')->default(0)->after('return_data');
            $table->timestamp('must_push_contact_unpaid_after_time')->nullable()->after('is_must_push_contact_unpaid');
            $table->timestamp('pushed_contact_unpaid_at')->nullable()->after('must_push_contact_unpaid_after_time');

            $table->boolean('is_must_send_sms_unpaid')->default(0)->after('pushed_contact_unpaid_at');
            $table->timestamp('must_send_sms_unpaid_after_time')->nullable()->after('is_must_send_sms_unpaid');
            $table->timestamp('sent_sms_unpaid_at')->nullable()->after('must_send_sms_unpaid_after_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
}

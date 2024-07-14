<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactLeadProcessReserveLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_lead_process_reserve_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('contact_lead_process_id')->default(0);
            $table->string('coupon_code', 191)->default('')->nullable();
            $table->string('phone', 20)->default('');
            $table->string('send_phone', 20)->default('');
            $table->string('event')->default('');

            $table->enum('status', [
                'create',
                'sent_sms_reserve',
            ]);

            $table->boolean('is_crm_pushed')->default(0);

            $table->timestamp('crm_pushed_at')->nullable()->default(null);
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
        Schema::dropIfExists('contact_lead_process_reserve_logs');
    }
}

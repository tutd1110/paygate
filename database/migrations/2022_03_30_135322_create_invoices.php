<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('landing_page_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('contact_lead_process_id')->default(0);
            $table->integer('amount')->default(0);
            $table->integer('discount')->default('0');
            $table->string('voucher_code')->default(0);
            $table->integer('quantity')->default(0);
            $table->enum('status', ['new', 'processing', 'paid'])->default('paid');
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
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
        Schema::dropIfExists('invoices');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->decimal('amount', '14', 2)->default(0)->change();
            $table->decimal('discount', '14', 2)->default(0)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('price', '14', 2)->default(0)->change();
            $table->decimal('discount', '14', 2)->default(0)->change();
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

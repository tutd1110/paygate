<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePgwOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pgw_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code',50);
            $table->string('partner_code',50);
            $table->integer('landingpage_id');
            $table->integer('contact_lead_process_id');
            $table->integer('order_client_id')->comment('Id hóa đơn thanh thanh toán của client');
            $table->decimal('amount',19,4)->comment('Số tiền của hóa đơn');
            $table->decimal('discount',19,4)->comment('số tiền được giảm giá')->nullable()->default(0);
            $table->string('coupon_code',25)->comment('Mã khuyến mại')->nullable();
            $table->integer('quantity')->comment('Số lượng sản phẩm trong đơn hàng')->default(1)->nullable();
            $table->enum('status', ['new','processing','waiting','paid','refund'])->default('new');
            $table->enum('is_api' , ['yes','no'])->default('yes');
            $table->text('return_url_true')->nullable();
            $table->text('return_url_false')->nullable();
            $table->text('return_data')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('pgw_order');
    }
}

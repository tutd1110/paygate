<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->comment('Tên của tin nhắn dùng để hiển thị trong trường hợp cần');
            $table->integer('parent_id')->comment('tin nhắn mặc định nếu landingpage không được cấu hình sẽ dùng loại nội dung này');
            $table->string('code')->comment('Định danh để truy vấn cho tin nhắn');
            $table->text('content')->comment('nội dung tin nhắn');
            $table->string('bind_param')->comment('Những param dùng trong tin nhắn');
            $table->integer('landing_page_id')->default(0);
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
        Schema::dropIfExists('message_templates');
    }
}

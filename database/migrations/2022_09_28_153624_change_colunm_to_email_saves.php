<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\{IntegerType,Type};
use Illuminate\Database\DBAL\{TimestampType};
class ChangeColunmToEmailSaves extends Migration
{
    public function __construct()
    {
        Type::hasType('enum') ?: Type::addType('enum', IntegerType::class);
        Type::hasType('timestamp') ?: Type::addType('timestamp', TimestampType::class);
    }

    public function up()
    {
        Schema::table('email_saves', function (Blueprint $table) {
            $table->timestamp('send_time')->nullable()->default(null)->change();
            $table->enum('send_error',[0,1])->default(0)->comment('0 là done, 1 là có lỗi')->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_saves', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\{StringType, Type};

class ChangeColunmStatusToEmailSaves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
            Type::hasType('enum') ?: Type::addType('enum', StringType::class);
    }
    public function up()
    {
        Schema::table('email_saves', function (Blueprint $table) {
            $table->enum('status', ['waiting','sent'])->default('waiting')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_saves');
    }
}

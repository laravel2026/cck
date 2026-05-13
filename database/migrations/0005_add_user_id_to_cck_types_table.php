<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cck_types', function (Blueprint $table) {
            $table->bigInteger('user_id')->default(0)->comment('创建人 ID')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('cck_types', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};

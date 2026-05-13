<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cck_types', function (Blueprint $table) {
            $table->boolean('api_auth_required')->default(true)->after('sort')->comment('API 接口是否需要登录（影响所有 CRUD 操作）');
            $table->boolean('api_own_only')->default(false)->after('api_auth_required')->comment('API 是否仅操作自己的内容');
        });
    }

    public function down(): void
    {
        Schema::table('cck_types', function (Blueprint $table) {
            $table->dropColumn(['api_auth_required', 'api_own_only']);
        });
    }
};

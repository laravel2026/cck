<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cck_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('菜单名');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级菜单');
            $table->unsignedBigInteger('cck_type_id')->nullable()->comment('关联内容类型');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->integer('sort')->default(0)->comment('排序');
            $table->boolean('is_active')->default(true)->comment('启用');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cck_menus');
    }
};

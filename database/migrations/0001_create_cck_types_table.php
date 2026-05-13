<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cck_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('机器名');
            $table->string('display_name', 200)->comment('显示名');
            $table->text('description')->nullable()->comment('描述说明');
            $table->string('icon', 50)->nullable()->comment('菜单图标 heroicon');
            $table->string('color', 20)->nullable()->comment('标签颜色');
            $table->boolean('show_in_menu')->default(false)->comment('是否显示在导航菜单');
            $table->string('menu_name', 200)->nullable()->comment('菜单显示名');
            $table->string('menu_parent', 200)->nullable()->comment('一级菜单名');
            $table->integer('menu_sort')->default(0)->comment('菜单排序');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->integer('sort')->default(0)->comment('类型排序');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cck_types');
    }
};

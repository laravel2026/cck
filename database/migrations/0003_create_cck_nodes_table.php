<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cck_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cck_type_id')->constrained('cck_types')->cascadeOnDelete()->comment('所属内容类型');
            $table->string('title', 200)->comment('标题');
            $table->string('slug', 200)->nullable()->unique()->comment('URL 别名');
            $table->bigInteger('user_id')->default(0)->comment('创建人 ID');
            $table->bigInteger('store_id')->default(0)->comment('商家 ID');
            $table->json('field_values')->nullable()->comment('字段值');
            $table->boolean('is_published')->default(true)->comment('是否发布');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cck_nodes');
    }
};

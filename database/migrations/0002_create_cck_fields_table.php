<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cck_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cck_type_id')->constrained('cck_types')->cascadeOnDelete()->comment('所属内容类型');
            $table->string('name', 100)->comment('机器名');
            $table->string('display_name', 200)->comment('显示名');
            $table->string('field_type', 50)->comment('字段类型 text|textarea|rich_editor|image|number|select|toggle|relation');
            $table->json('field_config')->nullable()->comment('字段配置');
            $table->boolean('is_required')->default(false)->comment('是否必填');
            $table->boolean('show_in_form')->default(true)->comment('表单显示');
            $table->boolean('show_in_list')->default(true)->comment('列表显示');
            $table->boolean('show_in_detail')->default(true)->comment('详情显示');
            $table->string('list_width', 20)->nullable()->comment('列表列宽');
            $table->boolean('list_sortable')->default(false)->comment('列表可排序');
            $table->integer('sort_order')->default(0)->comment('字段排序');
            $table->timestamps();

            $table->unique(['cck_type_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cck_fields');
    }
};

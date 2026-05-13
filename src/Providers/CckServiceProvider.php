<?php

namespace Modules\Cck\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Cck\Lib\FieldTypeRegistry;
use Modules\Cck\Lib\FieldTypes\ImageField;
use Modules\Cck\Lib\FieldTypes\NumberField;
use Modules\Cck\Lib\FieldTypes\RelationField;
use Modules\Cck\Lib\FieldTypes\RichEditorField;
use Modules\Cck\Lib\FieldTypes\SelectField;
use Modules\Cck\Lib\FieldTypes\TextField;
use Modules\Cck\Lib\FieldTypes\TextareaField;
use Modules\Cck\Lib\FieldTypes\ToggleField;
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;
use Modules\Cck\Policies\CckNodePolicy;
use Modules\Cck\Policies\CckTypePolicy;
use Modules\Settings\Lib\NavigationRegistry;

class CckServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册导航组
        NavigationRegistry::register('CCK 内容管理', 98);
    }

    public function boot(): void
    {
        // 加载迁移
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // 注册模型策略
        Gate::policy(CckType::class, CckTypePolicy::class);
        Gate::policy(CckNode::class, CckNodePolicy::class);

        // 注册字段类型
        FieldTypeRegistry::register('text', TextField::class);
        FieldTypeRegistry::register('textarea', TextareaField::class);
        FieldTypeRegistry::register('rich_editor', RichEditorField::class);
        FieldTypeRegistry::register('image', ImageField::class);
        FieldTypeRegistry::register('number', NumberField::class);
        FieldTypeRegistry::register('select', SelectField::class);
        FieldTypeRegistry::register('toggle', ToggleField::class);
        FieldTypeRegistry::register('relation', RelationField::class);

        // 在 Filament 渲染时注册动态导航菜单
        Filament::serving(function () {
            NavigationRegistry::sort();

            $panel = Filament::getPanel('admin');
            if (! $panel) {
                return;
            }

            try {
                $parents = \Modules\Cck\Models\CckMenu::with('children')
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort')
                    ->get();
            } catch (\Exception $e) {
                // 表不存在时跳过
                return;
            }

            $items = [];
            foreach ($parents as $parent) {
                foreach ($parent->children as $child) {
                    if (! $child->cck_type_id) {
                        continue;
                    }
                    $items[] = NavigationItem::make($child->name)
                        ->url(\Modules\Cck\Filament\Resources\CckNodeResource::getUrl('index', [
                            'cck_type_id' => $child->cck_type_id,
                        ]))
                        ->group($parent->name)
                        ->icon($child->icon ?: 'heroicon-o-document-text')
                        ->sort($child->sort);
                }
            }

            if (!empty($items)) {
                $panel->navigationItems($items);
            }
        });
    }
}

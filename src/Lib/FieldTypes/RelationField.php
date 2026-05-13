<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select as SelectConfig;
use Filament\Forms\Components\Toggle as ToggleInput;
use Modules\Cck\Models\CckField;
use Modules\Cck\Models\CckType;

class RelationField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '关联';
    }

    public static function build(CckField $field): Select
    {
        $config = $field->field_config ?? [];
        $targetTypeId = $config['target_cck_type_id'] ?? null;

        $options = [];
        if ($targetTypeId) {
            $type = CckType::find($targetTypeId);
            if ($type) {
                $options = $type->nodes()->where('is_published', true)->pluck('title', 'id')->toArray();
            }
        }

        $component = Select::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->options($options)
            ->searchable();

        if ($field->is_required) {
            $component->required();
        }
        if (!empty($config['multiple'])) {
            $component->multiple();
        }
        if (!empty($config['placeholder'])) {
            $component->placeholder($config['placeholder']);
        }

        return $component;
    }

    public static function table(CckField $field): TextColumn
    {
        $config = $field->field_config ?? [];

        $column = TextColumn::make('field_values.' . $field->name)
            ->label($field->display_name);

        if (!empty($config['multiple'])) {
            $column->badge();
        }
        if ($field->list_sortable) {
            $column->sortable();
        }
        if ($field->list_width) {
            $column->width($field->list_width);
        }

        return $column;
    }

    public static function infolist(CckField $field): TextEntry
    {
        $config = $field->field_config ?? [];

        $entry = TextEntry::make('field_values.' . $field->name)
            ->label($field->display_name);

        if (!empty($config['multiple'])) {
            $entry->badge();
        }

        return $entry;
    }

    public static function getConfigSchema(): array
    {
        return [
            SelectConfig::make('target_cck_type_id')
                ->label('目标内容类型')
                ->options(CckType::pluck('display_name', 'id'))
                ->required(),
            ToggleInput::make('multiple')
                ->label('多选'),
            TextInput::make('placeholder')
                ->label('占位符'),
        ];
    }
}

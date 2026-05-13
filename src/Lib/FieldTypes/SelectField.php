<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle as ToggleInput;
use Modules\Cck\Models\CckField;

class SelectField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '下拉选项';
    }

    public static function build(CckField $field): Select
    {
        $config = $field->field_config ?? [];
        $options = [];

        if (!empty($config['options']) && is_array($config['options'])) {
            foreach ($config['options'] as $option) {
                $options[$option['value']] = $option['label'];
            }
        }

        $component = Select::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->options($options);

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
            ToggleInput::make('multiple')
                ->label('多选'),
            TextInput::make('placeholder')
                ->label('占位符'),
            Repeater::make('options')
                ->label('选项列表')
                ->schema([
                    TextInput::make('label')
                        ->label('显示名')
                        ->required(),
                    TextInput::make('value')
                        ->label('值')
                        ->required(),
                ])
                ->addActionLabel('添加选项')
                ->defaultItems(1)
                ->grid(2),
        ];
    }
}

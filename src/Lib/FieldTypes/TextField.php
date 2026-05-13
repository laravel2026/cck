<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Modules\Cck\Models\CckField;

class TextField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '单行文本';
    }

    public static function build(CckField $field): TextInput
    {
        $config = $field->field_config ?? [];

        $component = TextInput::make('field_values.' . $field->name)
            ->label($field->display_name);

        if ($field->is_required) {
            $component->required();
        }
        if (!empty($config['min_length'])) {
            $component->minLength((int) $config['min_length']);
        }
        if (!empty($config['max_length'])) {
            $component->maxLength((int) $config['max_length']);
        }
        if (!empty($config['placeholder'])) {
            $component->placeholder($config['placeholder']);
        }
        if (array_key_exists('default_value', $config) && $config['default_value'] !== null && $config['default_value'] !== '') {
            $component->default($config['default_value']);
        }

        return $component;
    }

    public static function table(CckField $field): TextColumn
    {
        $config = $field->field_config ?? [];

        $column = TextColumn::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->limit(30);

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
        return TextEntry::make('field_values.' . $field->name)
            ->label($field->display_name);
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('min_length')
                ->label('最小长度')
                ->numeric()
                ->minValue(0),
            TextInput::make('max_length')
                ->label('最大长度')
                ->numeric()
                ->minValue(1),
            TextInput::make('placeholder')
                ->label('占位符'),
            TextInput::make('default_value')
                ->label('默认值'),
        ];
    }
}

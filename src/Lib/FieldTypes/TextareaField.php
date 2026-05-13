<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Modules\Cck\Models\CckField;

class TextareaField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '多行文本';
    }

    public static function build(CckField $field): Textarea
    {
        $config = $field->field_config ?? [];

        $component = Textarea::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->rows(4);

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

        return $component;
    }

    public static function table(CckField $field): TextColumn
    {
        $column = TextColumn::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->limit(50);

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
            ->label($field->display_name)
            ->markdown();
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
        ];
    }
}

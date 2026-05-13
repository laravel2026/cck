<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Modules\Cck\Models\CckField;

class NumberField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '数字';
    }

    public static function build(CckField $field): TextInput
    {
        $config = $field->field_config ?? [];

        $component = TextInput::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->numeric();

        if ($field->is_required) {
            $component->required();
        }
        if (array_key_exists('min', $config) && $config['min'] !== null && $config['min'] !== '') {
            $component->minValue((float) $config['min']);
        }
        if (array_key_exists('max', $config) && $config['max'] !== null && $config['max'] !== '') {
            $component->maxValue((float) $config['max']);
        }
        if (!empty($config['step'])) {
            $component->step((float) $config['step']);
        }
        if (!empty($config['prefix'])) {
            $component->prefix($config['prefix']);
        }
        if (!empty($config['suffix'])) {
            $component->suffix($config['suffix']);
        }

        return $component;
    }

    public static function table(CckField $field): TextColumn
    {
        $config = $field->field_config ?? [];

        $column = TextColumn::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->numeric();

        if (!empty($config['prefix'])) {
            $column->prefix($config['prefix']);
        }
        if (!empty($config['suffix'])) {
            $column->suffix($config['suffix']);
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
            ->label($field->display_name)
            ->numeric();

        if (!empty($config['prefix'])) {
            $entry->prefix($config['prefix']);
        }
        if (!empty($config['suffix'])) {
            $entry->suffix($config['suffix']);
        }

        return $entry;
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('min')
                ->label('最小值')
                ->numeric(),
            TextInput::make('max')
                ->label('最大值')
                ->numeric(),
            TextInput::make('step')
                ->label('步进值')
                ->numeric()
                ->default(1),
            TextInput::make('prefix')
                ->label('前缀（如 ¥）'),
            TextInput::make('suffix')
                ->label('后缀（如 元）'),
        ];
    }
}

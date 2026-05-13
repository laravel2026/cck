<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Modules\Cck\Models\CckField;

class ToggleField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '开关';
    }

    public static function build(CckField $field): Toggle
    {
        $config = $field->field_config ?? [];

        $component = Toggle::make('field_values.' . $field->name)
            ->label($field->display_name);

        if (!empty($config['on_label'])) {
            $component->onLabel($config['on_label']);
        }
        if (!empty($config['off_label'])) {
            $component->offLabel($config['off_label']);
        }
        if (array_key_exists('default_value', $config) && $config['default_value'] !== null) {
            $component->default((bool) $config['default_value']);
        }

        return $component;
    }

    public static function table(CckField $field): IconColumn
    {
        $column = IconColumn::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->boolean();

        if ($field->list_width) {
            $column->width($field->list_width);
        }

        return $column;
    }

    public static function infolist(CckField $field): IconEntry
    {
        return IconEntry::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->boolean();
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('on_label')
                ->label('开启标签'),
            TextInput::make('off_label')
                ->label('关闭标签'),
        ];
    }
}

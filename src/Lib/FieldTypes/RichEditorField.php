<?php

namespace Modules\Cck\Lib\FieldTypes;

use Modules\Tools\Forms\Components\RichEditorPlus;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Modules\Cck\Models\CckField;

class RichEditorField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '富文本';
    }

    public static function build(CckField $field): \Filament\Forms\Components\RichEditor
    {
        $config = $field->field_config ?? [];

        $component = RichEditorPlus::make('field_values.' . $field->name, $field->display_name);

        if ($field->is_required) {
            $component->required();
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
            ->html()
            ->limit(80);

        if ($field->list_width) {
            $column->width($field->list_width);
        }

        return $column;
    }

    public static function infolist(CckField $field): TextEntry
    {
        return TextEntry::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->html();
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('max_length')
                ->label('最大长度')
                ->numeric()
                ->minValue(1),
            Select::make('toolbar')
                ->label('工具栏')
                ->options([
                    'basic' => '简洁',
                    'full' => '完整',
                ])
                ->default('basic'),
            TextInput::make('placeholder')
                ->label('占位符'),
        ];
    }
}

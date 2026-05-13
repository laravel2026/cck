<?php

namespace Modules\Cck\Lib\FieldTypes;

use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Modules\Cck\Models\CckField;

class ImageField implements FieldTypeInterface
{
    public static function getLabel(): string
    {
        return '图片';
    }

    public static function build(CckField $field): FileUpload
    {
        $config = $field->field_config ?? [];

        $component = FileUpload::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->image();

        if ($field->is_required) {
            $component->required();
        }
        if (!empty($config['multiple'])) {
            $component->multiple();
            if (!empty($config['max_files'])) {
                $component->maxFiles((int) $config['max_files']);
            }
        }
        if (!empty($config['max_size_mb'])) {
            $component->maxSize((int) $config['max_size_mb'] * 1024);
        }
        if (!empty($config['accepted_types']) && is_array($config['accepted_types'])) {
            $component->acceptedFileTypes($config['accepted_types']);
        }

        return $component;
    }

    public static function table(CckField $field): ImageColumn
    {
        $config = $field->field_config ?? [];

        $column = ImageColumn::make('field_values.' . $field->name)
            ->label($field->display_name)
            ->size(60);

        if (!empty($config['multiple'])) {
            $column->stacked()->limit(3);
        }
        if ($field->list_width) {
            $column->width($field->list_width);
        }

        return $column;
    }

    public static function infolist(CckField $field): ImageEntry
    {
        $config = $field->field_config ?? [];

        $entry = ImageEntry::make('field_values.' . $field->name)
            ->label($field->display_name);

        if (!empty($config['multiple'])) {
            $entry->simpleLightbox();
        }

        return $entry;
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('max_size_mb')
                ->label('最大大小 (MB)')
                ->numeric()
                ->minValue(1)
                ->default(2),
            Toggle::make('multiple')
                ->label('允许多图'),
            TextInput::make('max_files')
                ->label('最多几张')
                ->numeric()
                ->minValue(1)
                ->visible(fn($get) => $get('multiple')),
        ];
    }
}

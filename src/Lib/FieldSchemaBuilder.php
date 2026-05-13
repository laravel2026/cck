<?php

namespace Modules\Cck\Lib;

use Modules\Cck\Models\CckType;

class FieldSchemaBuilder
{
    public static function buildForm(CckType $type): array
    {
        $components = [];
        foreach ($type->fields()->where('show_in_form', true)->orderBy('sort_order')->get() as $field) {
            $handler = FieldTypeRegistry::get($field->field_type);
            if ($handler) {
                $components[] = $handler::build($field);
            }
        }
        return $components;
    }

    public static function buildTable(CckType $type): array
    {
        $columns = [];
        foreach ($type->fields()->where('show_in_list', true)->orderBy('sort_order')->get() as $field) {
            $handler = FieldTypeRegistry::get($field->field_type);
            if ($handler) {
                $columns[] = $handler::table($field);
            }
        }
        return $columns;
    }

    public static function buildInfolist(CckType $type): array
    {
        $entries = [];
        foreach ($type->fields()->where('show_in_detail', true)->orderBy('sort_order')->get() as $field) {
            $handler = FieldTypeRegistry::get($field->field_type);
            if ($handler) {
                $entries[] = $handler::infolist($field);
            }
        }
        return $entries;
    }
}

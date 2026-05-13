<?php

namespace Modules\Cck\Lib;

class FieldConfigManager
{
    /**
     * 根据字段类型获取配置表单
     */
    public static function getConfigSchema(string $fieldType): array
    {
        $handler = FieldTypeRegistry::get($fieldType);
        if (! $handler) {
            return [];
        }
        return $handler::getConfigSchema();
    }
}

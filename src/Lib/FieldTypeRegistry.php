<?php

namespace Modules\Cck\Lib;

use Modules\Cck\Lib\FieldTypes\FieldTypeInterface;

class FieldTypeRegistry
{
    private static array $types = [];

    public static function register(string $type, string $handlerClass): void
    {
        static::$types[$type] = $handlerClass;
    }

    public static function get(string $type): ?FieldTypeInterface
    {
        $class = static::$types[$type] ?? null;
        if (! $class || ! class_exists($class)) {
            return null;
        }
        return new $class;
    }

    public static function all(): array
    {
        return static::$types;
    }

    public static function options(): array
    {
        $options = [];
        foreach (static::$types as $type => $class) {
            if (method_exists($class, 'getLabel')) {
                $options[$type] = $class::getLabel();
            }
        }
        return $options;
    }
}

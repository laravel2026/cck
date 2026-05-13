<?php

/**
 * 版权所有 ©Laravel2026, Inc. 保留所有权利。
 * https://github.com/laravel2026
 * E-mail: laravel2026@163.com
 */

namespace Modules\Cck\Lib;

use Modules\Cck\Models\CckField;
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;

/**
 * CCK 内容格式化器
 *
 * 将 CckNode 按字段定义解析后输出为结构化数组，
 * 字段值根据类型自动处理（图片转 URL、下拉转 label、关联查目标等）。
 */
class CckNodeFormatter
{
    /**
     * 格式化单个节点
     *
     * @param CckNode $node
     * @param CckType|null $type 可选，传入避免重复查询
     * @return array
     */
    public static function format(CckNode $node, ?CckType $type = null): array
    {
        $type ??= $node->type;
        if (! $type) {
            return self::baseData($node);
        }

        $fields = $type->fields()->orderBy('sort_order')->get();
        $fieldValues = $node->field_values ?? [];

        $dynamic = [];
        foreach ($fields as $field) {
            $value = $fieldValues[$field->name] ?? null;
            $dynamic[$field->name] = [
                'name' => $field->name,
                'display_name' => $field->display_name,
                'field_type' => $field->field_type,
                'value' => self::resolveFieldValue($field, $value),
            ];
        }

        return array_merge(self::baseData($node), [
            'type' => [
                'id' => $type->id,
                'name' => $type->name,
                'display_name' => $type->display_name,
            ],
            'fields' => $dynamic,
        ]);
    }

    /**
     * 格式化多条节点
     */
    public static function formatMany(iterable $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[] = self::format($node);
        }
        return $result;
    }

    /**
     * 节点基本信息
     */
    private static function baseData(CckNode $node): array
    {
        return [
            'id' => $node->id,
            'title' => $node->title,
            'slug' => $node->slug,
            'is_published' => $node->is_published,
            'sort' => $node->sort,
            'published_at' => $node->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $node->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $node->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 按字段类型解析值
     */
    private static function resolveFieldValue(CckField $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $config = $field->field_config ?? [];

        return match ($field->field_type) {
            'image' => self::resolveImage($config, $value),
            'select' => self::resolveSelect($config, $value),
            'relation' => self::resolveRelation($config, $value),
            'toggle' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : $value,
            default => $value,
        };
    }

    /**
     * 图片字段：路径转完整 URL
     */
    private static function resolveImage(array $config, mixed $value): mixed
    {
        $multiple = ! empty($config['multiple']);

        if ($multiple && is_array($value)) {
            return array_map(fn($v) => self::imageUrl($v), $value);
        }

        if (is_array($value)) {
            return self::imageUrl($value[0] ?? '');
        }

        return self::imageUrl($value);
    }

    /**
     * 下拉选项字段：返回 label + value
     */
    private static function resolveSelect(array $config, mixed $value): mixed
    {
        $multiple = ! empty($config['multiple']);
        $options = [];
        foreach (($config['options'] ?? []) as $opt) {
            $options[$opt['value']] = $opt['label'];
        }

        if ($multiple && is_array($value)) {
            $result = [];
            foreach ($value as $v) {
                $result[] = ['value' => $v, 'label' => $options[$v] ?? $v];
            }
            return $result;
        }

        $single = is_array($value) ? ($value[0] ?? '') : $value;
        return ['value' => $single, 'label' => $options[$single] ?? $single];
    }

    /**
     * 关联字段：解析目标节点标题
     */
    private static function resolveRelation(array $config, mixed $value): mixed
    {
        $multiple = ! empty($config['multiple']);

        if ($multiple && is_array($value)) {
            $result = [];
            foreach ($value as $id) {
                $result[] = self::resolveRelationNode($id);
            }
            return $result;
        }

        $id = is_array($value) ? ($value[0] ?? null) : $value;
        return $id ? self::resolveRelationNode($id) : null;
    }

    /**
     * 根据 ID 获取关联节点的基本信息
     */
    private static function resolveRelationNode(mixed $id): ?array
    {
        if (! $id) {
            return null;
        }
        $target = CckNode::find((int) $id);
        if (! $target) {
            return ['id' => (int) $id, 'title' => null];
        }
        return [
            'id' => $target->id,
            'title' => $target->title,
        ];
    }

    /**
     * 图片路径转完整 URL
     */
    private static function imageUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }
        if (function_exists('cdn_url')) {
            return cdn_url($path);
        }
        // fallback: 项目无 cdn_url 时使用 storage URL
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }
}

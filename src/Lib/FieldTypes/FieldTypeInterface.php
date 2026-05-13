<?php

namespace Modules\Cck\Lib\FieldTypes;

use Modules\Cck\Models\CckField;

interface FieldTypeInterface
{
    /** 获取字段类型显示名 */
    public static function getLabel(): string;

    /** 构建表单组件 */
    public static function build(CckField $field): mixed;

    /** 构建列表列 */
    public static function table(CckField $field): mixed;

    /** 构建详情展示 */
    public static function infolist(CckField $field): mixed;

    /** 获取字段类型的配置表单 Schema */
    public static function getConfigSchema(): array;
}

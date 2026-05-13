<?php

namespace Modules\Cck\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Cck\Lib\CckNodeFormatter;

class CckNode extends Model
{
    protected $table = 'cck_nodes';

    protected $fillable = [
        'cck_type_id',
        'title',
        'slug',
        'user_id',
        'store_id',
        'field_values',
        'is_published',
        'sort',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'field_values' => 'array',
            'is_published' => 'boolean',
            'sort' => 'integer',
            'published_at' => 'datetime',
            'user_id' => 'integer',
            'store_id' => 'integer',
        ];
    }

    public function type()
    {
        return $this->belongsTo(CckType::class, 'cck_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 按内容类型 slug 查询
     */
    public function scopeByType($query, string $slug)
    {
        return $query->whereHas('type', fn($q) => $q->where('name', $slug));
    }

    /**
     * 序列化时自动格式化：字段值自动处理（图片转 URL、下拉转 label 等）
     */
    public function toArray()
    {
        return CckNodeFormatter::format($this);
    }
}

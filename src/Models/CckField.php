<?php

namespace Modules\Cck\Models;

use Illuminate\Database\Eloquent\Model;

class CckField extends Model
{
    protected $table = 'cck_fields';

    protected $fillable = [
        'cck_type_id',
        'name',
        'display_name',
        'field_type',
        'field_config',
        'is_required',
        'show_in_form',
        'show_in_list',
        'show_in_detail',
        'list_width',
        'list_sortable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'field_config' => 'array',
            'is_required' => 'boolean',
            'show_in_form' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_detail' => 'boolean',
            'list_sortable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function type()
    {
        return $this->belongsTo(CckType::class, 'cck_type_id');
    }
}

<?php

namespace Modules\Cck\Models;

use Illuminate\Database\Eloquent\Model;

class CckMenu extends Model
{
    protected $table = 'cck_menus';

    protected $fillable = [
        'name',
        'parent_id',
        'cck_type_id',
        'icon',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(CckMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CckMenu::class, 'parent_id')->orderBy('sort');
    }

    public function cckType()
    {
        return $this->belongsTo(CckType::class, 'cck_type_id');
    }
}

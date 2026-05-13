<?php

namespace Modules\Cck\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CckType extends Model
{
    protected $table = 'cck_types';

    protected $fillable = [
        'user_id',
        'name',
        'display_name',
        'description',
        'icon',
        'color',
        'show_in_menu',
        'menu_name',
        'menu_parent',
        'menu_sort',
        'is_active',
        'sort',
        'api_auth_required',
        'api_own_only',
    ];

    protected function casts(): array
    {
        return [
            'show_in_menu' => 'boolean',
            'is_active' => 'boolean',
            'sort' => 'integer',
            'menu_sort' => 'integer',
            'api_auth_required' => 'boolean',
            'api_own_only' => 'boolean',
        ];
    }

    public function fields()
    {
        return $this->hasMany(CckField::class, 'cck_type_id');
    }

    public function nodes()
    {
        return $this->hasMany(CckNode::class, 'cck_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

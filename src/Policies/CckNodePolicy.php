<?php

namespace Modules\Cck\Policies;

use Illuminate\Contracts\Auth\Authenticatable as AuthUser;
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;
use Modules\Users\Policies\ResourcePolicy;

class CckNodePolicy extends ResourcePolicy
{
    public function viewAny(AuthUser $user, $model = null): bool
    {
        if ((int) $user->getAuthIdentifier() === 1) {
            return true;
        }

        $slug = $this->resolveTypeNameFromRequest();
        if ($slug && $user->hasPermission($slug . '.viewAny', false)) {
            return $user->hasPermission($slug . '.viewAny');
        }

        return $user->hasPermission('cck-node.viewAny');
    }

    public function create(AuthUser $user, $model = null): bool
    {
        if ((int) $user->getAuthIdentifier() === 1) {
            return true;
        }

        $slug = $this->resolveTypeNameFromRequest();
        if ($slug && $user->hasPermission($slug . '.create', false)) {
            return $user->hasPermission($slug . '.create');
        }

        return $user->hasPermission('cck-node.create');
    }

    protected function slugForModel($model): string
    {
        if ($model instanceof CckNode && $model->type) {
            return $model->type->name;
        }
        return 'cck-node';
    }

    private function resolveTypeNameFromRequest(): ?string
    {
        $typeId = request('cck_type_id') ?? request('record.cck_type_id');
        if ($typeId) {
            $type = CckType::find($typeId);
            return $type?->name;
        }
        return null;
    }
}

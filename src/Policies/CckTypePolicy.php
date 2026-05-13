<?php

namespace Modules\Cck\Policies;

use Illuminate\Contracts\Auth\Authenticatable as AuthUser;
use Modules\Users\Policies\ResourcePolicy;

class CckTypePolicy extends ResourcePolicy
{
    protected function slugForModel(string $modelClass): ?string
    {
        return 'cck-type';
    }
}

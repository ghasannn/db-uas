<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Scope query if user belongs to an organization and is not a superadmin
            if ($user->isOrganizer() && $user->organization_id) {
                $builder->where($model->getTable() . '.organization_id', $user->organization_id);
            }
        }
    }
}

<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // No aplicar si es SuperAdmin (ve todos los tenants)
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            return;
        }

        // Aplicar filtro por tenant_id
        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where($model->getTable() . '.tenant_id', auth()->user()->tenant_id);
        }
    }

    public function extend(Builder $builder): void
    {
        // Agregar macro para ignorar el scope (solo SuperAdmin)
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Automatically scopes queries to the currently resolved tenant, and fills
 * tenant_id on new records if it wasn't already set. Only activates when
 * ResolveTenant middleware has bound a tenant into the container (real web
 * requests) — console commands, jobs, and artisan scripts that loop across
 * tenants manually are unaffected, same as before this trait existed.
 *
 * This is a safety net on top of, not a replacement for, the existing manual
 * ->where('tenant_id', ...) filters throughout the app. Those stay in place
 * for now and are only removed in a later, separate step.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('tenant') && ($tenant = app('tenant'))) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->tenant_id) && app()->bound('tenant') && ($tenant = app('tenant'))) {
                $model->tenant_id = $tenant->id;
            }
        });
    }
}

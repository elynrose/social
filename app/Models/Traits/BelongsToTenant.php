<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait to automatically scope models by the current tenant.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            if ($tenant) {
                $table = (new static)->getTable();
                if ($table && \Schema::hasColumn($table, 'tenant_id')) {
                    $builder->where($table . '.tenant_id', $tenant->id);
                }
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
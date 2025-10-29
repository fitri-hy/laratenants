<?php

namespace MultiTenant\Traits;

use MultiTenant\Services\TenantManager;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            $tenant = TenantManager::getTenant();
            if ($tenant) {
                $model->tenant_id = $tenant->id;
            }
        });
    }
}

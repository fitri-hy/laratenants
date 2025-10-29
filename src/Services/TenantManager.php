<?php

namespace MultiTenant\Services;

use MultiTenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    protected static ?Tenant $currentTenant = null;

    public static function setTenant(Tenant $tenant)
    {
        static::$currentTenant = $tenant;

        if ($tenant->database) {
            config(['database.connections.tenant.database' => $tenant->database]);
            DB::purge('tenant');
        }

        if (session()->isStarted()) {
            session()->setId(config('multi-tenant.session_prefix', 'tenant_') . $tenant->id);
        }
    }

    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant;
    }

	public static function resolveTenant(string $host, string $path = null): ?Tenant
	{
		$tenants = Tenant::all();

		foreach ($tenants as $tenant) {
			if ($tenant->isWildcard()) {
				$pattern = str_replace('{tenant}', '([a-z0-9\-]+)', $tenant->domain);
				if (preg_match("#^$pattern$#i", $host)) {
					return $tenant;
				}
			} elseif ($tenant->domain === $host) {
				return $tenant;
			}
		}

		return config('multi-tenant.default_tenant') ? Tenant::find(config('multi-tenant.default_tenant')) : null;
	}
}
